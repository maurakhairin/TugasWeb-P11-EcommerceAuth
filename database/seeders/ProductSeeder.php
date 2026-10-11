<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Database\Factories\ProductFactory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Category::orderBy('id')->get() as $category) {

            $jenisProduk = ProductFactory::$katalog[$category->nama] ?? [];

            foreach ($jenisProduk as $namaJenis => [$hargaMin, $hargaMax]) {
                Product::factory()
                    ->for($category) // category_id diisi dari kategori yang sedang diproses
                    ->create([
                        'nama' => $namaJenis.' '.fake()->randomElement(ProductFactory::$varian),
                        'harga' => fake()->numberBetween($hargaMin, $hargaMax) * 1000,
                    ]);
            }
        }
        
        Product::inRandomOrder()
            ->take(5)
            ->get()
            ->each(fn (Product $product) => $product->update(['stok' => 0]));
    }
}
