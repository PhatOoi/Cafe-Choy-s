<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    // Chỉ cho phép admin đi tiếp, staff/customer sẽ bị chặn hoặc điều hướng phù hợp.
    public function handle(Request $request, Closure $next)
    {
        // Nếu chưa đăng nhập thì quay về màn hình login.
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        // Lấy role hiện tại để kiểm tra quyền truy cập khu vực quản trị.
        $roleId = auth()->user()->role_id;

        // role_id: 1 = admin, 2 = staff, 3 = customer
        if ($roleId !== 1) {
            // Staff bị chuyển về dashboard staff thay vì trả 403 cứng.
            if ($roleId === 2) {
                return redirect()->route('staff.dashboard')
                    ->with('error', 'Bạn không có quyền truy cập trang quản trị.');
            }

            // Customer hoặc vai trò khác không được phép vào vùng admin.
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
