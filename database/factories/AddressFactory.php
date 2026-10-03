<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement(['Rumah', 'Kantor', 'Kos']),
            'recipient' => fake()->name(),
            'phone' => '08'.fake()->numerify('##########'),
            'street' => fake()->streetAddress(),
            'city' => fake()->randomElement(['Medan', 'Binjai', 'Deli Serdang', 'Pematangsiantar', 'Tebing Tinggi']),
            'province' => 'Sumatera Utara',
            'postal_code' => fake()->numerify('2####'),
            'is_default' => false,
        ];
    }
}
