@extends('layouts.shop')

@section('title', 'Admin')

@section('content')

    <h1 class="text-2xl font-bold mb-6">Ringkasan Admin</h1>
    <p class="text-sm text-gray-500 mb-6">
        Halaman ini hanya bisa dibuka role <strong>admin</strong> (dijaga middleware <code>role:admin</code>).
        Editor dan user biasa akan mendapat 403.
    </p>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg border p-4">
            <p class="text-xs text-gray-500">Total Produk</p>
            <p class="text-2xl font-bold">{{ $jumlahProduk }}</p>
        </div>
        <div class="bg-white rounded-lg border p-4">
            <p class="text-xs text-gray-500">Produk Stok Habis</p>
            <p class="text-2xl font-bold">{{ $produkHabis }}</p>
        </div>
        <div class="bg-white rounded-lg border p-4">
            <p class="text-xs text-gray-500">Total Order</p>
            <p class="text-2xl font-bold">{{ $jumlahOrder }}</p>
        </div>
        <div class="bg-white rounded-lg border p-4">
            <p class="text-xs text-gray-500">User per Role</p>
            @foreach ($jumlahPerRole as $role => $total)
                <p class="text-sm">{{ $role }}: <strong>{{ $total }}</strong></p>
            @endforeach
        </div>
    </div>

    <h2 class="text-lg font-semibold mb-3">5 Produk Terpopuler</h2>
    <div class="bg-white rounded-lg border divide-y">
        @forelse ($produkTerpopuler as $product)
            <div class="px-4 py-3 flex justify-between text-sm">
                <span>{{ $product->nama }}</span>
                {{-- order_items_count dibuat otomatis oleh withCount('orderItems') di scopePopular() --}}
                <span class="text-gray-500">{{ $product->order_items_count }}x dipesan</span>
            </div>
        @empty
            <p class="px-4 py-3 text-sm text-gray-400">Belum ada data order.</p>
        @endforelse
    </div>

    <p class="mt-6 text-sm">
        <a href="{{ route('admin.demo.eager') }}" class="text-indigo-600 hover:underline">
            Lihat demo Eager Loading vs N+1 (JSON) &rarr;
        </a>
    </p>

@endsection
