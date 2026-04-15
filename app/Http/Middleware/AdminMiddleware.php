<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        $roleId = auth()->user()->role_id;

        // role_id: 1 = admin, 2 = staff, 3 = customer
        if ($roleId !== 1) {
            if ($roleId === 2) {
                return redirect()->route('staff.dashboard')
                    ->with('error', 'Bạn không có quyền truy cập trang quản trị.');
            }
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
