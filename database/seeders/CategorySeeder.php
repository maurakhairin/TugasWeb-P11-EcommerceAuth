<?php

namespace Database\Seeders;

use App\Models\Category;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (CategoryFactory::$namaKategori as $nama) {
            Category::create([
                'nama' => $nama,
                'slug' => Str::slug($nama),
            ]);
        }
    }
}
