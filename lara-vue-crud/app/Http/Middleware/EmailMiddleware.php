<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EmailMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Example: Only allow authenticated users
        if (!auth()->check()) {
            abort(403, 'Unauthorized');
        }
        return $next($request);
    }
}
