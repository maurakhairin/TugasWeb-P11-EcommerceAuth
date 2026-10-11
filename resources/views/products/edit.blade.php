@extends('layouts.shop')

@section('title', 'Edit Produk')

@section('content')

    <h1 class="text-2xl font-bold mb-6">Edit Produk</h1>

    <form method="POST" action="{{ route('products.update', $product) }}"
          class="bg-white rounded-lg border border-gray-200 p-6 max-w-xl space-y-4">
        @csrf
        @method('PUT') {{-- HTML form hanya mengenal GET/POST, jadi PUT "dipalsukan" lewat field tersembunyi ini --}}

        <div>
            <label for="category_id" class="block text-sm font-medium">Kategori</label>
            <select id="category_id" name="category_id" class="mt-1 w-full rounded border-gray-300">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"
                        @selected(old('category_id', $product->category_id) == $category->id)>
                        {{ $category->nama }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="nama" class="block text-sm font-medium">Nama Produk</label>
            <input id="nama" name="nama" type="text" value="{{ old('nama', $product->nama) }}"
                   class="mt-1 w-full rounded border-gray-300">
            @error('nama') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="deskripsi" class="block text-sm font-medium">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="3"
                      class="mt-1 w-full rounded border-gray-300">{{ old('deskripsi', $product->deskripsi) }}</textarea>
            @error('deskripsi') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="harga" class="block text-sm font-medium">Harga (Rp)</label>
                <input id="harga" name="harga" type="number" min="0" value="{{ old('harga', $product->harga) }}"
                       class="mt-1 w-full rounded border-gray-300">
                @error('harga') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="stok" class="block text-sm font-medium">Stok</label>
                <input id="stok" name="stok" type="number" min="0" value="{{ old('stok', $product->stok) }}"
                       class="mt-1 w-full rounded border-gray-300">
                @error('stok') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white text-sm">Simpan</button>
            <a href="{{ route('products.show', $product) }}" class="text-sm text-gray-600 hover:underline">Batal</a>
        </div>
    </form>

@endsection
