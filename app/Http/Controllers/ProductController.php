<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Manajemen produk untuk admin & editor (URL /kelola/products).
 * Route diproteksi middleware 'auth' + 'role:admin,editor'; setiap aksi
 * ke satu produk dicek lagi oleh ProductPolicy (editor hanya produk miliknya).
 */
class ProductController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Product::class);

        $user = $request->user();

        $products = Product::query()
            ->with(['category', 'owner:id,name'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id)) // editor: milik sendiri
            ->search($request->query('q'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('manage.products.index', compact('products'));
    }

    public function create(): View
    {
        Gate::authorize('create', Product::class);

        return view('manage.products.create', ['categories' => Category::orderBy('name')->get()]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        Gate::authorize('create', Product::class);

        $data = $request->validated();
        $data['slug'] = Product::makeSlug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['user_id'] = $request->user()->id; // dari sesi login, BUKAN dari input form

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('manage.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        Gate::authorize('update', $product);

        return view('manage.products.edit', [
            'product'    => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            // ganti gambar: hapus file lama (jika bukan URL luar)
            if ($product->image && ! str_starts_with($product->image, 'http')) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        } else {
            unset($data['image']); // tidak upload baru -> pertahankan gambar lama
        }

        $product->update($data); // slug dibiarkan agar URL produk tidak berubah

        return redirect()->route('manage.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);

        $product->delete(); // soft delete: riwayat pesanan tetap utuh

        return redirect()->route('manage.products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
