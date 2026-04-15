<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\DailyRevenueSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Controller hiển thị lịch sử đơn và cho khách tự hủy đơn khi còn hợp lệ.
class OrderHistoryController extends Controller
{
    // Đồng bộ snapshot doanh thu sau khi trạng thái đơn thay đổi.
    private function syncDailyRevenueSnapshots(): void
    {
        app(DailyRevenueSnapshotService::class)->syncLastDays(30);
    }

    // Trang lịch sử đơn hàng của khách.
    public function index()
    {
        // Lấy user hiện tại để giới hạn dữ liệu đúng người và đúng vai trò.
        $user = Auth::user();

        // Nhân viên không dùng trang này nên chuyển thẳng về dashboard staff.
        if ($user && $user->isStaff()) {
            return redirect()->route('staff.dashboard')->with('error', 'Nhân viên không có quyền truy cập lịch sử đơn hàng.');
        }

        // Lấy toàn bộ đơn của khách, ưu tiên các đơn đã thanh toán hoặc đơn không có payment record.
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

        // Nếu session còn lưu một đơn QR pending cũ nhưng payment thực tế đã paid,
        // dọn cart và cờ pending để navbar/giao diện không hiển thị sai trạng thái.
        $pendingQrOrderId = session('pending_qr_order_id');
        if ($pendingQrOrderId) {
            $payment = \App\Models\Payment::where('order_id', $pendingQrOrderId)->first();
            if ($payment && $payment->status === 'paid') {
                session()->forget(['cart', 'pending_qr_order_id']);
            }
        }

        // Cart count dùng để render badge giỏ hàng trên giao diện khách.
        $cart = session()->get('cart', []);
        $cartCount = array_sum(array_column($cart, 'qty'));

        return view('order-history', compact('orders', 'cartCount'));
    }

    // Cho khách tự hủy đơn của mình nếu đơn chưa sang bước chuẩn bị.
    public function cancel(Request $request, $id)
    {
        $user = Auth::user();

        // Nhân viên không được phép đi qua flow hủy của khách.
        if ($user && $user->isStaff()) {
            return redirect()->route('staff.dashboard')->with('error', 'Nhân viên không có quyền hủy đơn tại đây.');
        }

        // Chỉ lấy đơn thuộc về user hiện tại để tránh truy cập chéo dữ liệu.
        $order = Order::query()
            ->where('user_id', $user->id)
            ->whereKey($id)
            ->firstOrFail();

        // Backend chặn cứng không cho hủy khi đơn đã sang processing hoặc các bước sau.
        if (!$order->canCustomerCancel()) {
            return back()->with('error', 'Đơn hàng đã chuyển sang đang chuẩn bị nên bạn không thể hủy nữa.');
        }

        // Ghi dấu vết thao tác hủy của khách vào note đơn hàng.
        $existingNote = trim((string) $order->note);
        $order->status = 'cancelled';
        $order->note = trim(($existingNote !== '' ? $existingNote . ' | ' : '') . 'Khách hàng tự hủy đơn');
        $order->save();

        // Đồng bộ số liệu báo cáo nếu trạng thái đơn thay đổi ảnh hưởng đến thống kê.
        $this->syncDailyRevenueSnapshots();

        return back()->with('success', 'Đã hủy đơn hàng thành công.');
    }
}