<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrder extends Model
{
    protected $fillable = [
        'order_id',
        'order_date',
        'status',
        'customer_id',
        'admin_id',
        'items',
        'subtotal',
        'discount',
        'taxes',
        'grand_total',
    ];

    protected $casts = [
        'order_date' => 'date',
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'taxes' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function calculateTotals(): void
    {
        if (!$this->items) {
            $this->subtotal = 0;
            $this->discount = 0;
            $this->taxes = 0;
            $this->grand_total = 0;
            return;
        }

        $this->subtotal = collect($this->items)->sum(function ($item) {
            return ($item['units'] ?? 0) * ($item['unit_price'] ?? 0);
        });

        $this->discount = collect($this->items)->sum('discount') ?? 0;
        $this->taxes = collect($this->items)->sum('taxes') ?? 0;
        $this->grand_total = $this->subtotal - $this->discount + $this->taxes;
    }

    public function getItemsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setItemsAttribute($value)
    {
        $this->attributes['items'] = json_encode($value);
    }
}
