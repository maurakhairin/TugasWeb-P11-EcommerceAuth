<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderDemoSeeder extends Seeder
{
    public function run(): void
    {
        $pelanggan = User::where('role', 'user')->first();

        if (! $pelanggan) {
            return;
        }

        $cart = Cart::create(['user_id' => $pelanggan->id]);

        Product::inStock()->inRandomOrder()->take(3)->get()->each(function (Product $product) use ($cart) {
            $cart->items()->create([
                'product_id' => $product->id,
                'qty' => fake()->numberBetween(1, 3),
            ]);
        });

        User::factory()->count(5)->create()->each(function (User $user) {
            Order::factory()
                ->count(3)
                ->for($user)
                ->create()
                ->each(function (Order $order) {
                    $produkDipilih = Product::inRandomOrder()->take(fake()->numberBetween(1, 4))->get();
                    $totalOrder = 0;

                    foreach ($produkDipilih as $product) {
                        $qty = fake()->numberBetween(1, 3);

                        $order->items()->create([
                            'product_id' => $product->id,
                            'qty' => $qty,
                            'harga_saat_beli' => $product->harga,
                        ]);

                        $totalOrder += $qty * $product->harga;
                    }

                    $order->update(['total' => $totalOrder]);
                });
        });
    }
}
