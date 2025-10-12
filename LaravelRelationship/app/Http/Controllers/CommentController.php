<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Store a newly created comment.
     */
    public function store(Request $request, Assignment $assignment)
    {
        $request->validate([
            'body' => 'required|string|max:1000'
        ]);

        $comment = $assignment->comments()->create([
            'user_id' => Auth::id(),
            'body' => $request->body
        ]);

        return back()->with('success', 'Comment added successfully!');
    }
}
