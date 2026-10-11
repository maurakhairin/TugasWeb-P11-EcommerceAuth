<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public static array $katalog = [
        'Elektronik' => [
            'Laptop' => [4500, 15000],
            'Headset' => [80, 800],
            'Mouse Wireless' => [50, 400],
            'Keyboard Mekanik' => [250, 1500],
            'Power Bank' => [100, 600],
            'Speaker Bluetooth' => [150, 1200],
            'Smartwatch' => [300, 2500],
            'Webcam' => [150, 900],
        ],
        'Fashion Pria' => [
            'Kaos Pria' => [50, 200],
            'Kemeja Pria' => [120, 450],
            'Celana Chino' => [150, 450],
            'Jaket Bomber' => [200, 800],
            'Sepatu Sneakers Pria' => [250, 1200],
            'Topi Baseball' => [40, 200],
            'Ikat Pinggang' => [60, 300],
            'Jam Tangan Pria' => [150, 1500],
        ],
        'Fashion Wanita' => [
            'Blouse' => [100, 400],
            'Dress' => [150, 700],
            'Rok Plisket' => [80, 300],
            'Hijab Pashmina' => [40, 250],
            'Tas Selempang Wanita' => [120, 700],
            'Sepatu Flat' => [150, 600],
            'Cardigan' => [100, 400],
            'Dompet Wanita' => [60, 350],
        ],
        'Makanan & Minuman' => [
            'Kopi Bubuk' => [40, 180],
            'Teh Celup' => [10, 60],
            'Minyak Goreng' => [20, 50],
            'Madu Murni' => [60, 250],
            'Biskuit' => [8, 40],
            'Keripik Singkong' => [10, 40],
            'Susu UHT' => [15, 60],
            'Mie Instan' => [3, 10],
        ],
        'Perlengkapan Rumah Tangga' => [
            'Blender' => [150, 900],
            'Rice Cooker' => [250, 1200],
            'Kursi Lipat' => [80, 400],
            'Meja Lipat' => [120, 600],
            'Lampu Belajar' => [50, 300],
            'Rak Susun' => [100, 600],
            'Panci Stainless' => [80, 500],
            'Setrika' => [120, 500],
        ],
        'Olahraga & Outdoor' => [
            'Sepatu Lari' => [250, 1500],
            'Matras Yoga' => [80, 350],
            'Dumbbell' => [100, 600],
            'Tenda Camping' => [300, 2000],
            'Tas Gunung' => [250, 1500],
            'Botol Minum Olahraga' => [40, 250],
            'Raket Badminton' => [80, 800],
            'Bola Futsal' => [100, 450],
        ],
        'Kesehatan & Kecantikan' => [
            'Sabun Mandi' => [5, 40],
            'Sunscreen' => [40, 250],
            'Serum Wajah' => [60, 400],
            'Masker Wajah' => [20, 150],
            'Vitamin C' => [40, 250],
            'Hand Sanitizer' => [10, 60],
            'Shampoo' => [20, 120],
            'Termometer Digital' => [50, 300],
        ],
        'Buku & Alat Tulis' => [
            'Buku Catatan' => [10, 80],
            'Pulpen' => [3, 30],
            'Pensil Warna' => [15, 120],
            'Stabilo' => [5, 40],
            'Novel' => [40, 150],
            'Buku Pelajaran' => [50, 200],
            'Kalkulator Ilmiah' => [80, 400],
            'Map Plastik' => [2, 15],
        ],
    ];

    public static array $varian = [
        'Premium', 'Original', 'Klasik', 'Edisi Terbaru', 'Pro',
        'Lite', 'Plus', 'Eksklusif', 'Standar', 'Hemat',
    ];

    public function definition(): array
    {

        $jenis = null;
        $pilihJenis = function (int $categoryId) use (&$jenis): array {
            return $jenis ??= $this->pilihJenis($categoryId);
        };

        return [

            'category_id' => fn () => Category::query()->inRandomOrder()->value('id')
                ?? Category::factory()->create()->id,

            'nama' => fn (array $atribut) => $pilihJenis($atribut['category_id'])['nama']
                .' '.fake()->randomElement(self::$varian),

            'slug' => fn (array $atribut) => Str::slug($atribut['nama'])
                .'-'.fake()->unique()->numberBetween(1000, 9999),

            'deskripsi' => fake()->sentence(10),

            'harga' => function (array $atribut) use ($pilihJenis) {
                $rentang = $pilihJenis($atribut['category_id']);

                return fake()->numberBetween($rentang['min'], $rentang['max']) * 1000;
            },

            'stok' => fake()->numberBetween(0, 150),
            'gambar' => null,
        ];
    }

    protected function pilihJenis(int $categoryId): array
    {
        $namaKategori = Category::query()->whereKey($categoryId)->value('nama');
        $daftar = self::$katalog[$namaKategori] ?? [];

        if ($daftar === []) {
            return ['nama' => 'Produk '.$namaKategori, 'min' => 15, 'max' => 500];
        }

        $nama = fake()->randomElement(array_keys($daftar));
        [$min, $max] = $daftar[$nama];

        return ['nama' => $nama, 'min' => $min, 'max' => $max];
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stok' => 0,
        ]);
    }
}
