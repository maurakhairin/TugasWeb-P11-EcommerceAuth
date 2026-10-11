<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{

    public static array $namaKategori = [
        'Elektronik',
        'Fashion Pria',
        'Fashion Wanita',
        'Makanan & Minuman',
        'Perlengkapan Rumah Tangga',
        'Olahraga & Outdoor',
        'Kesehatan & Kecantikan',
        'Buku & Alat Tulis',
    ];

    public function definition(): array
    {
        $nama = fake()->randomElement(self::$namaKategori);

        return [
            'nama' => $nama,
            'slug' => Str::slug($nama).'-'.fake()->unique()->numberBetween(100, 999),
        ];
    }
}
