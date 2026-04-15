<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware kiểm tra tài khoản có đang active không.
 * Chặn các tài khoản bị admin vô hiệu hóa (is_active = false).
 */
class CheckActiveUser
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && !auth()->user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('error', 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ quản lý.');
        }

        return $next($request);
    }
}
