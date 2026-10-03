<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/** 54 produk (9 per kategori), harga dalam Rupiah, dimiliki bergantian oleh 2 editor. */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Elektronik' => [
                ['Smartphone Xiaomi Redmi Note 13 8/256 GB', 3299000],
                ['Laptop ASUS Vivobook 14 Ryzen 5', 7499000],
                ['Headphone Bluetooth Sony WH-CH520', 699000],
                ['Power Bank Anker 10000 mAh', 329000],
                ['Smartwatch Amazfit Bip 5', 899000],
                ['Keyboard Mekanikal Rexus Daxa M84', 459000],
                ['Mouse Wireless Logitech M331 Silent', 219000],
                ['Speaker Bluetooth JBL Go 3', 599000],
                ['Monitor LG 24 Inci Full HD IPS', 1599000],
            ],
            'Fashion Pria' => [
                ['Kemeja Flanel Kotak Lengan Panjang', 189000],
                ['Kaos Polos Cotton Combed 30s', 69000],
                ['Celana Chino Slim Fit Pria', 249000],
                ['Jaket Bomber Pria Waterproof', 329000],
                ['Sepatu Sneakers Putih Pria', 399000],
                ['Jam Tangan Analog Strap Kulit', 279000],
                ['Ikat Pinggang Kulit Asli', 129000],
                ['Topi Baseball Polos Adjustable', 79000],
                ['Hoodie Zipper Fleece Pria', 289000],
            ],
            'Fashion Wanita' => [
                ['Dress Midi Floral Rayon', 259000],
                ['Blouse Katun Lengan Balon', 149000],
                ['Hijab Pashmina Ceruty Babydoll', 59000],
                ['Rok Plisket Panjang', 119000],
                ['Tas Selempang Wanita Kulit Sintetis', 219000],
                ['Sepatu Flat Shoes Wanita', 179000],
                ['Cardigan Rajut Oversize', 169000],
                ['Gamis Syari Polos Busui', 299000],
                ['Celana Kulot Highwaist', 139000],
            ],
            'Rumah Tangga' => [
                ['Rice Cooker Digital 1.8 Liter', 399000],
                ['Blender Kaca 2 Liter 3 Kecepatan', 349000],
                ['Set Panci Stainless Steel 5 Pcs', 459000],
                ['Dispenser Air Galon Bawah', 899000],
                ['Setrika Uap Anti Lengket', 289000],
                ['Set Sapu dan Pel Putar', 119000],
                ['Lampu LED 12 Watt Isi 4 Pcs', 89000],
                ['Rak Sepatu Susun 4 Tingkat', 159000],
                ['Kipas Angin Berdiri 16 Inci', 329000],
            ],
            'Olahraga' => [
                ['Matras Yoga Anti Slip 6 mm', 109000],
                ['Dumbbell Set Vinyl 10 Kg', 229000],
                ['Sepatu Lari Pria Ringan', 449000],
                ['Bola Sepak Ukuran 5', 159000],
                ['Raket Badminton Karbon Full Set', 299000],
                ['Botol Minum Olahraga 1 Liter', 59000],
                ['Tali Skipping Speed Rope', 39000],
                ['Tas Gym Duffel Waterproof', 189000],
                ['Sarung Tangan Gym Anti Slip', 69000],
            ],
            'Buku & Alat Tulis' => [
                ['Buku Panduan Laravel untuk Pemula', 89000],
                ['Buku Algoritma dan Struktur Data', 119000],
                ['Buku Dasar-Dasar Basis Data', 99000],
                ['Pulpen Gel Hitam 0.5 mm Isi 12', 36000],
                ['Buku Tulis Isi 38 Lembar Paket 10', 45000],
                ['Stabilo Highlighter Set 6 Warna', 49000],
                ['Tas Ransel Laptop 15 Inci', 249000],
                ['Kalkulator Scientific 240 Fungsi', 189000],
                ['Planner Mingguan 2026', 69000],
            ],
        ];

        $editorIds = User::where('role', User::ROLE_EDITOR)->pluck('id');
        $i = 0;
        $n = 0; // nomor urut produk = ID; dipakai untuk mencocokkan file products/{n}.webp

        foreach ($catalog as $categoryName => $items) {
            $category = Category::where('name', $categoryName)->firstOrFail();

            foreach ($items as [$name, $price]) {
                $n++;
                $imagePath = "products/{$n}.webp";

                Product::create([
                    'category_id' => $category->id,
                    'user_id'     => $editorIds[$i++ % $editorIds->count()],
                    'name'        => $name,
                    'slug'        => Str::slug($name),
                    'description' => "{$name} — produk {$categoryName} pilihan dengan kualitas terjamin. "
                        .'Garansi toko 7 hari dan pengiriman ke seluruh Indonesia.',
                    'image'       => file_exists(storage_path('app/public/'.$imagePath)) ? $imagePath : null,
                    'price'       => $price,
                    'stock'       => fake()->numberBetween(5, 120),
                    'is_active'   => true,
                ]);
            }
        }
    }
}
