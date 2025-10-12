<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'body',
        'commentable_id',
        'commentable_type'
    ];

    /**
     * Get the parent commentable model (Assignment, etc.).
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
