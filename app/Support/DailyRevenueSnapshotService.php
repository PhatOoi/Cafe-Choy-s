<?php

namespace App\Support;

use App\Models\DailyRevenue;
use App\Models\Order;

class DailyRevenueSnapshotService
{
    // Đồng bộ snapshot doanh thu của N ngày gần nhất vào bảng daily_revenues.
    public function syncLastDays(int $days = 30): void
    {
        // Chuẩn hóa số ngày và xác định khoảng thời gian cần làm mới dữ liệu.
        $days = max(1, $days);
        $startDate = now()->subDays($days - 1)->startOfDay();
        $endDate = now()->startOfDay();

        // Xóa snapshot cũ vượt ngoài cửa sổ lịch sử đang cần giữ lại.
        DailyRevenue::where('revenue_date', '<', $startDate->toDateString())->delete();

        $cursorDate = $startDate->copy();

        // Duyệt từng ngày để tính và upsert snapshot riêng cho ngày đó.
        while ($cursorDate->lte($endDate)) {
            $dateString = $cursorDate->toDateString();

            // Chỉ lấy các đơn đã paid và thuộc 2 nguồn doanh thu cần thống kê.
            $orders = Order::with(['payment', 'user'])
                ->whereDate('created_at', $dateString)
                ->whereHas('payment', fn ($query) => $query->where('status', 'paid'))
                ->where(function ($query) {
                    $query->where(function ($staffQuery) {
                        $staffQuery->whereNotNull('assigned_staff_id')
                            ->where('order_type', 'in_store');
                    })->orWhereHas('user', fn ($userQuery) => $userQuery->where('role_id', 3));
                })
                ->get();

            // Tách doanh thu theo nguồn tạo đơn và phương thức thanh toán để view report dùng trực tiếp.
            $payload = [
                'total_orders' => $orders->count(),
                'total_revenue' => (float) $orders->sum('final_price'),
                'staff_created_revenue' => (float) $orders
                    ->filter(fn ($order) => !is_null($order->assigned_staff_id) && $order->order_type === 'in_store')
                    ->sum('final_price'),
                'customer_revenue' => (float) $orders
                    ->filter(fn ($order) => optional($order->user)->role_id === 3)
                    ->sum('final_price'),
                'cash_revenue' => (float) $orders
                    ->filter(fn ($order) => optional($order->payment)->method === 'cash')
                    ->sum('final_price'),
                'transfer_revenue' => (float) $orders
                    ->filter(fn ($order) => optional($order->payment)->method === 'bank_transfer')
                    ->sum('final_price'),
            ];

            // Upsert để cùng một ngày chỉ có một snapshot mới nhất.
            DailyRevenue::updateOrCreate(
                ['revenue_date' => $dateString],
                $payload
            );

            // Tiếp tục sang ngày kế tiếp trong khoảng cần sync.
            $cursorDate->addDay();
        }
    }
}