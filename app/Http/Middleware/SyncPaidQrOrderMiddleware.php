<?php

namespace App\Http\Middleware;

use App\Models\Payment;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SyncPaidQrOrderMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->isStaff()) {
            return $next($request);
        }

        $pendingOrderId = session('pending_qr_order_id');

        if (!$pendingOrderId) {
            return $next($request);
        }

        $payment = Payment::where('order_id', $pendingOrderId)->first();

        if (!$payment || $payment->status !== 'paid') {
            return $next($request);
        }

        DB::table('carts')->where('user_id', auth()->id())->delete();
        session()->forget(['cart', 'pending_qr_order_id']);

        if ($request->routeIs('cart.index') || $request->is('cart')) {
            return redirect()->route('orders.history')->with('success', 'Đơn hàng đã được nhân viên xác nhận thanh toán và chuyển vào lịch sử đơn hàng.');
        }

        return $next($request);
    }
}