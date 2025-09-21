<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class HandleExceptions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Model not found: ' . $e->getMessage(), [
                'url' => $request->url(),
                'method' => $request->method(),
                'user_id' => auth()->id(),
                'ip' => $request->ip()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Resource not found'], 404);
            }
            
            return response()->view('errors.404', [], 404);
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unhandled exception: ' . $e->getMessage(), [
                'url' => $request->url(),
                'method' => $request->method(),
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Internal server error'], 500);
            }
            
            return response()->view('errors.500', [], 500);
        }
    }
}
