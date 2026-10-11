<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class EagerLoadingDemoController extends Controller
{
    public function __invoke(): JsonResponse
    {
        DB::enableQueryLog();

        DB::flushQueryLog();
        $mulaiLazy = microtime(true);

        $orders = Order::latest('id')->take(10)->get();

        $hasilLazy = $orders->map(fn (Order $order) => [
            'order_id' => $order->id,
            'pelanggan' => $order->user->name,
            'produk' => $order->items
                ->map(fn ($item) => $item->product->nama)
                ->all(),
        ]);

        $logLazy = DB::getQueryLog();
        $waktuLazy = round((microtime(true) - $mulaiLazy) * 1000, 2);

        DB::flushQueryLog();
        $mulaiEager = microtime(true);

        $orders = Order::with(['user', 'items.product'])
            ->latest('id')
            ->take(10)
            ->get();

        $hasilEager = $orders->map(fn (Order $order) => [
            'order_id' => $order->id,
            'pelanggan' => $order->user->name,
            'produk' => $order->items
                ->map(fn ($item) => $item->product->nama)
                ->all(),
        ]);

        $logEager = DB::getQueryLog();
        $waktuEager = round((microtime(true) - $mulaiEager) * 1000, 2);

        DB::disableQueryLog();

        return response()->json([
            'penjelasan' => 'Dua cara mengambil data yang SAMA. Bandingkan jumlah query-nya.',
            'lazy_loading_n_plus_1' => [
                'jumlah_query' => count($logLazy),
                'waktu_ms' => $waktuLazy,
                '4_query_pertama' => array_slice(array_column($logLazy, 'query'), 0, 4),
            ],
            'eager_loading_with' => [
                'jumlah_query' => count($logEager),
                'waktu_ms' => $waktuEager,
                'semua_query' => array_column($logEager, 'query'),
            ],
            'data_sama' => $hasilLazy->all() === $hasilEager->all(),
        ]);
    }
}