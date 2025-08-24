<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['name', 'price', 'description'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function productListings(): HasMany
    {
        return $this->hasMany(ProductListing::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($product) {
            if ($product->wasChanged('price')) {
                $product->productListings()->update(['price' => $product->price]);
            }
        });
    }
}
