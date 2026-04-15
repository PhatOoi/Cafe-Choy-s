<?php

namespace App\Support;

use App\Models\DailyRevenue;
use App\Models\Order;

class DailyRevenueSnapshotService
{
    public function syncLastDays(int $days = 30): void
    {
        $days = max(1, $days);
        $startDate = now()->subDays($days - 1)->startOfDay();
        $endDate = now()->startOfDay();

        DailyRevenue::where('revenue_date', '<', $startDate->toDateString())->delete();

        $cursorDate = $startDate->copy();

        while ($cursorDate->lte($endDate)) {
            $dateString = $cursorDate->toDateString();

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

            DailyRevenue::updateOrCreate(
                ['revenue_date' => $dateString],
                $payload
            );

            $cursorDate->addDay();
        }
    }
}