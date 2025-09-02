<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    protected $fillable = [
        'subject', 'to', 'cc', 'body', 'customer_id'
    ];

    protected $casts = [
        'to' => 'array',
        'cc' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
