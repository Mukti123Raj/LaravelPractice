<?php

namespace App\Observers;

use App\Events\CommentCreated;
use App\Models\Comment;

class CommentObserver
{
    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        event(new CommentCreated($comment));
    }
}
