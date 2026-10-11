<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with('category')
            ->inStock()
            ->latest()
            ->paginate(12);

        return view('products.index', [
            'products' => $products,
        ]);
    }

    public function show(Product $product): View
    {
        $product->load('category');

        return view('products.show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product): View
    {
        Gate::authorize('update', $product);

        $categories = Category::orderBy('nama')->get();

        return view('products.edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'harga' => ['required', 'integer', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
        ]);

        $product->update($validated);

        return redirect()
            ->route('products.show', $product)
            ->with('success', "Produk \"{$product->nama}\" berhasil diupdate.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);

        if ($product->orderItems()->exists()) {
            return redirect()
                ->route('products.show', $product)
                ->with('error', "Produk \"{$product->nama}\" tidak bisa dihapus karena sudah tercatat di riwayat order.");
        }

        $nama = $product->nama;
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', "Produk \"{$nama}\" berhasil dihapus.");
    }
}