<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'product_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($productListing) {
            if ($productListing->product) {
                $productListing->price = $productListing->product->price;
                $productListing->seller_id = $productListing->product->seller_id;
            }
        });

        static::updated(function ($productListing) {
            if ($productListing->wasChanged('product_id')) {
                $productListing->price = $productListing->product->price;
                $productListing->seller_id = $productListing->product->seller_id;
                $productListing->saveQuietly();
            }
        });
    }
}
