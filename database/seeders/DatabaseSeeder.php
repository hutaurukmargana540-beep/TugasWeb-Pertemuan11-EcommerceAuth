<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,      // 1 admin, 2 editor, 1 user demo + 7 user acak
            CategorySeeder::class,  // 6 kategori
            ProductSeeder::class,   // 54 produk realistis
            OrderSeeder::class,     // alamat, pesanan, item, pembayaran, review
        ]);
    }
}
