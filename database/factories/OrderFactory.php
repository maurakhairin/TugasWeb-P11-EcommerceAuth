<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => fake()->randomElement(['pending', 'diproses', 'selesai', 'dibatalkan']),
            'total' => fake()->numberBetween(50, 5000) * 1000,
            'alamat_pengiriman' => fake()->address(),
        ];
    }
}
