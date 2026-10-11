<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'jumlahPerRole' => User::selectRaw('role, COUNT(*) as total')
                ->groupBy('role')
                ->pluck('total', 'role'),

            'jumlahProduk' => Product::count(),
            'produkHabis' => Product::where('stok', 0)->count(),
            'jumlahOrder' => Order::count(),

            'produkTerpopuler' => Product::popular()->take(5)->get(),
        ]);
    }
}