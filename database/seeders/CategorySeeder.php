<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Elektronik'          => 'Gawai, aksesori, dan perangkat elektronik harian.',
            'Fashion Pria'        => 'Pakaian dan aksesori untuk pria.',
            'Fashion Wanita'      => 'Pakaian dan aksesori untuk wanita.',
            'Rumah Tangga'        => 'Perlengkapan dapur dan rumah.',
            'Olahraga'            => 'Peralatan olahraga dan kebugaran.',
            'Buku & Alat Tulis'   => 'Buku pelajaran, alat tulis, dan perlengkapan belajar.',
        ];

        foreach ($categories as $name => $description) {
            Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $description,
            ]);
        }
    }
}
