<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $comments = [
            'Barang sesuai deskripsi, pengiriman cepat.',
            'Kualitas bagus untuk harganya.',
            'Packing rapi dan aman, recommended.',
            'Sesuai ekspektasi, terima kasih.',
        ];

        User::where('role', User::ROLE_USER)->get()->each(function (User $user) use ($products, $comments) {
            $address = Address::factory()->for($user)->create(['is_default' => true]);

            foreach (range(1, rand(1, 3)) as $n) {
                $status = fake()->randomElement(Order::STATUSES);

                $order = Order::create([
                    'order_number' => 'INV-'.strtoupper(Str::random(8)),
                    'user_id'      => $user->id,
                    'address_id'   => $address->id,
                    'status'       => $status,
                    'total'        => 0,
                ]);

                $total = 0;
                foreach ($products->random(rand(1, 4)) as $product) {
                    $qty = rand(1, 3);
                    $order->items()->create([
                        'product_id' => $product->id,
                        'quantity'   => $qty,
                        'price'      => $product->price, // snapshot harga saat checkout
                    ]);
                    $total += $product->price * $qty;

                    if ($status === 'completed') {
                        Review::updateOrCreate(
                            ['user_id' => $user->id, 'product_id' => $product->id],
                            ['rating' => rand(3, 5), 'comment' => $comments[array_rand($comments)]]
                        );
                    }
                }

                $order->update(['total' => $total]);

                if ($status !== 'cancelled') {
                    $paid = in_array($status, ['paid', 'shipped', 'completed'], true);
                    $order->payment()->create([
                        'method'  => fake()->randomElement(['transfer_bank', 'qris', 'cod']),
                        'amount'  => $total,
                        'status'  => $paid ? 'paid' : 'unpaid',
                        'paid_at' => $paid ? now()->subDays(rand(0, 20)) : null,
                    ]);
                }
            }
        });
    }
}
