<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StaffMiddleware
{
    // Cho phép staff và admin truy cập các route vận hành nội bộ.
    public function handle(Request $request, Closure $next)
    {
        // Bắt buộc phải đăng nhập trước khi vào khu staff.
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        // Đọc role hiện tại để xác định có thuộc nhóm nội bộ hay không.
        $roleId = auth()->user()->role_id;

        // role_id: 1 = admin, 2 = staff, 3 = customer
        if (!in_array($roleId, [1, 2])) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
