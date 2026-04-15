<?php

namespace App\Http\Middleware;

use App\Models\Payment;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SyncPaidQrOrderMiddleware
{
    // Đồng bộ session/cart của khách khi đơn QR treo đã được staff xác nhận paid.
    public function handle(Request $request, Closure $next): Response
    {
        // Chỉ áp dụng cho khách đã đăng nhập; staff không dùng flow này.
        if (!auth()->check() || auth()->user()->isStaff()) {
            return $next($request);
        }

        // Lấy id đơn QR đang chờ xác nhận được lưu trong session.
        $pendingOrderId = session('pending_qr_order_id');

        if (!$pendingOrderId) {
            return $next($request);
        }

        // Kiểm tra payment thật trong database xem đã được staff chuyển sang paid chưa.
        $payment = Payment::where('order_id', $pendingOrderId)->first();

        if (!$payment || $payment->status !== 'paid') {
            return $next($request);
        }

        // Khi đã paid thì xóa cart database và session cũ để tránh UI treo đơn pending.
        DB::table('carts')->where('user_id', auth()->id())->delete();
        session()->forget(['cart', 'pending_qr_order_id']);

        // Nếu khách đang đứng ở trang cart thì điều hướng thẳng sang lịch sử đơn kèm thông báo.
        if ($request->routeIs('cart.index') || $request->is('cart')) {
            return redirect()->route('orders.history')->with('success', 'Đơn hàng đã được nhân viên xác nhận thanh toán và chuyển vào lịch sử đơn hàng.');
        }

        return $next($request);
    }
}