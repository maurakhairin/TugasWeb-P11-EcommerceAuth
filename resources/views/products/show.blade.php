@extends('layouts.shop')

@section('title', $product->nama)

@section('content')

    <a href="{{ route('products.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Kembali ke daftar</a>

    <div class="bg-white rounded-lg border border-gray-200 p-6 mt-4">
        <p class="text-xs text-gray-500">{{ $product->category->nama }}</p>
        <h1 class="text-2xl font-bold mt-1">{{ $product->nama }}</h1>
        <p class="text-indigo-600 font-bold text-xl mt-3">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
        <p class="text-sm text-gray-500 mt-1">Stok: {{ $product->stok }}</p>
        <p class="mt-4">{{ $product->deskripsi }}</p>

        {{--
            @can memanggil ProductPolicy. Tombol hanya DITAMPILKAN kalau policy mengizinkan.
            Ini cuma soal tampilan — penjagaan yang sebenarnya tetap ada di server
            (route middleware + Gate::authorize di ProductController).
        --}}
        <div class="mt-6 flex items-center gap-3">
            @can('update', $product)
                <a href="{{ route('products.edit', $product) }}"
                   class="px-4 py-2 rounded bg-amber-500 text-white text-sm">Edit</a>
            @endcan

            @can('delete', $product)
                <form method="POST" action="{{ route('products.destroy', $product) }}"
                      onsubmit="return confirm('Yakin hapus produk ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded bg-red-600 text-white text-sm">Hapus</button>
                </form>
            @endcan
        </div>
    </div>

@endsection
