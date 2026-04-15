<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderHistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user && $user->isStaff()) {
            return redirect()->route('staff.dashboard')->with('error', 'Nhân viên không có quyền truy cập lịch sử đơn hàng.');
        }

        $orders = Order::with(['items.product', 'items.extras'])
            ->where('user_id', $user->id)
            ->where(function ($query) {
                $query->doesntHave('payment')
                    ->orWhereHas('payment', function ($paymentQuery) {
                        $paymentQuery->where('status', 'paid');
                    });
            })
            ->latest('created_at')
            ->get();

        $pendingQrOrderId = session('pending_qr_order_id');
        if ($pendingQrOrderId) {
            $payment = \App\Models\Payment::where('order_id', $pendingQrOrderId)->first();
            if ($payment && $payment->status === 'paid') {
                session()->forget(['cart', 'pending_qr_order_id']);
            }
        }

        $cart = session()->get('cart', []);
        $cartCount = array_sum(array_column($cart, 'qty'));

        return view('order-history', compact('orders', 'cartCount'));
    }
}