<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Cập nhật last_seen_at mỗi khi user đã xác thực gửi request, dùng để theo dõi online/offline.
class UpdateLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            // Chỉ update mỗi 1 phút để tránh ghi DB quá nhiều.
            $user = Auth::user();
            if ($user->last_seen_at === null || $user->last_seen_at->diffInSeconds(now()) > 60) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['last_seen_at' => now()]);
            }
        }

        return $next($request);
    }
}
