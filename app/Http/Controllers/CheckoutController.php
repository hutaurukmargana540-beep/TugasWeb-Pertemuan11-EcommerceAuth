<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Beli langsung: satu produk per pesanan.
 * Membuat orders + order_items + payments dalam SATU transaksi database,
 * lalu mengurangi stok produk.
 */
class CheckoutController extends Controller
{
    public const PAYMENT_METHODS = [
        'transfer_bank' => 'Transfer Bank',
        'qris'          => 'QRIS',
        'cod'           => 'Bayar di Tempat (COD)',
    ];

    public function create(Request $request, Product $product): View|RedirectResponse
    {
        abort_unless($product->is_active, 404);

        if ($product->stock < 1) {
            return redirect()->route('shop.show', $product)->with('error', 'Maaf, stok produk ini habis.');
        }

        $addresses = $request->user()->addresses()->orderByDesc('is_default')->orderBy('id')->get();
        $quantity = max(1, min((int) $request->query('qty', 1), $product->stock));
        $methods = self::PAYMENT_METHODS;

        return view('checkout.create', compact('product', 'addresses', 'quantity', 'methods'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $user = $request->user();

        $data = $request->validate([
            'quantity'       => ['required', 'integer', 'min:1', 'max:1000'],
            // alamat lama harus milik user yang sedang login (cegah memakai alamat orang lain)
            'address_id'     => ['nullable', 'integer', Rule::exists('addresses', 'id')->where('user_id', $user->id)],
            // alamat baru: wajib diisi kalau tidak memilih alamat lama
            'recipient'      => ['required_without:address_id', 'nullable', 'string', 'max:100'],
            'phone'          => ['required_without:address_id', 'nullable', 'regex:/^[0-9+\-\s]{8,30}$/'],
            'street'         => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'city'           => ['required_without:address_id', 'nullable', 'string', 'max:100'],
            'province'       => ['required_without:address_id', 'nullable', 'string', 'max:100'],
            'postal_code'    => ['required_without:address_id', 'nullable', 'string', 'max:10'],
            'payment_method' => ['required', Rule::in(array_keys(self::PAYMENT_METHODS))],
            'notes'          => ['nullable', 'string', 'max:500'],
        ], [
            'quantity.required'                => 'Jumlah wajib diisi.',
            'quantity.min'                     => 'Jumlah minimal 1.',
            'address_id.exists'                => 'Alamat tidak valid.',
            'recipient.required_without'       => 'Nama penerima wajib diisi.',
            'phone.required_without'           => 'Nomor telepon wajib diisi.',
            'phone.regex'                      => 'Nomor telepon tidak valid.',
            'street.required_without'          => 'Alamat lengkap wajib diisi.',
            'city.required_without'            => 'Kota wajib diisi.',
            'province.required_without'        => 'Provinsi wajib diisi.',
            'postal_code.required_without'     => 'Kode pos wajib diisi.',
            'payment_method.required'          => 'Pilih metode pembayaran.',
            'payment_method.in'                => 'Metode pembayaran tidak valid.',
        ]);

        $quantity = (int) $data['quantity'];

        $order = DB::transaction(function () use ($product, $user, $data, $quantity) {
            // kunci baris produk supaya dua pembeli tidak melewati batas stok bersamaan
            $locked = Product::whereKey($product->id)->lockForUpdate()->first();

            if (! $locked || ! $locked->is_active || $locked->stock < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stok tidak mencukupi. Stok tersedia: '.($locked?->stock ?? 0).'.',
                ]);
            }

            if (! empty($data['address_id'])) {
                $address = $user->addresses()->findOrFail($data['address_id']);
            } else {
                $address = $user->addresses()->create([
                    'label'       => 'Rumah',
                    'recipient'   => $data['recipient'],
                    'phone'       => $data['phone'],
                    'street'      => $data['street'],
                    'city'        => $data['city'],
                    'province'    => $data['province'],
                    'postal_code' => $data['postal_code'],
                    'is_default'  => ! $user->addresses()->exists(),
                ]);
            }

            $total = (float) $locked->price * $quantity;

            $order = Order::create([
                'order_number' => $this->newOrderNumber(),
                'user_id'      => $user->id,
                'address_id'   => $address->id,
                'status'       => 'pending',
                'total'        => $total,
                'notes'        => $data['notes'] ?? null,
            ]);

            $order->items()->create([
                'product_id' => $locked->id,
                'quantity'   => $quantity,
                'price'      => $locked->price, // snapshot harga saat checkout
            ]);

            $order->payment()->create([
                'method' => $data['payment_method'],
                'amount' => $total,
                'status' => 'unpaid',
            ]);

            $locked->decrement('stock', $quantity);

            return $order;
        });

        return redirect()->route('orders.show', $order)
            ->with('success', 'Pesanan '.$order->order_number.' berhasil dibuat. Silakan lakukan pembayaran.');
    }

    private function newOrderNumber(): string
    {
        do {
            $number = 'INV-'.strtoupper(Str::random(8));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
