<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Product.
 * Dibuat lewat: php artisan make:model Product -mf
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'nama',
        'slug',
        'deskripsi',
        'harga',
        'stok',
        'gambar',
    ];

    // ---------------------------------------------------------------
    // RELASI
    // ---------------------------------------------------------------

    /**
     * Relasi MANY-TO-ONE (kebalikan dari Category::products()):
     * satu produk HANYA punya satu kategori.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi langsung ke baris cart_items (dipakai internal, lebih
     * sering pakai carts() di bawah untuk kebutuhan tampilan).
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function carts(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class, 'cart_items')
            ->withPivot('qty')
            ->withTimestamps();
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot('qty', 'harga_saat_beli')
            ->withTimestamps();
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stok', '>', 0);
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->withCount('orderItems')
            ->orderByDesc('order_items_count');
    }
}
