<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Authenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            // Nếu request yêu cầu JSON, trả về 401
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            // Nếu không, redirect đến login
            return redirect('/login');
        }

        return $next($request);
    }
}
