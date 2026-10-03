<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = ucwords(fake()->unique()->words(3, true));

        return [
            'category_id' => Category::factory(),
            'user_id' => User::factory()->editor(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'description' => fake()->sentence(12),
            'price' => fake()->numberBetween(20, 5000) * 1000,
            'stock' => fake()->numberBetween(1, 100),
            'is_active' => true,
        ];
    }
}
