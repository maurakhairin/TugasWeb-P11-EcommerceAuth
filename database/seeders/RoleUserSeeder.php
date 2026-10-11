<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleUserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin Utama',
            'email' => 'admin@ecommerce.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Editor Toko',
            'email' => 'editor@ecommerce.test',
            'password' => Hash::make('password'),
            'role' => 'editor',
        ]);

        User::factory()->create([
            'name' => 'Pelanggan Biasa',
            'email' => 'user@ecommerce.test',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
    }
}
