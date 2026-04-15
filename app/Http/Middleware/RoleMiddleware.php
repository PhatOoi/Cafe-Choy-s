<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware phân quyền linh hoạt theo role.
 *
 * Cách dùng trong routes:
 *   ->middleware('role:admin')
 *   ->middleware('role:admin,staff')
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return redirect('/login')->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        $user       = auth()->user();
        $userRole   = $user->role->name ?? null; // 'admin' | 'staff' | 'customer'

        if (!in_array($userRole, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Forbidden.'], 403);
            }

            // Redirect thông minh về đúng trang của role đó
            return match ($user->role_id) {
                1 => redirect('/admin')->with('error', 'Bạn không có quyền vào trang này.'),
                2 => redirect()->route('staff.dashboard')->with('error', 'Bạn không có quyền vào trang này.'),
                default => redirect('/')->with('error', 'Bạn không có quyền vào trang này.'),
            };
        }

        return $next($request);
    }
}
