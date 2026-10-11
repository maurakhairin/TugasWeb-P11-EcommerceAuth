@extends('layouts.shop')

@section('title', 'Daftar Produk')

@section('content')

    <h1 class="text-2xl font-bold mb-6">Daftar Produk</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($products as $product)
            <a href="{{ route('products.show', $product) }}"
               class="block bg-white rounded-lg border border-gray-200 p-4 hover:shadow">
                {{-- category sudah di-eager-load di controller (with('category')), jadi aman dipanggil di loop --}}
                <p class="text-xs text-gray-500">{{ $product->category->nama }}</p>
                <h2 class="font-semibold mt-1">{{ $product->nama }}</h2>
                <p class="mt-2 text-indigo-600 font-bold">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500 mt-1">Stok: {{ $product->stok }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $products->links() }}
    </div>

@endsection
