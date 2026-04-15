<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyRevenue extends Model
{
    // Bảng snapshot doanh thu ngày cho staff report.
    protected $table = 'daily_revenues';

    // Các cột được phép ghi hàng loạt khi service đồng bộ snapshot.
    protected $fillable = [
        'revenue_date',
        'total_orders',
        'total_revenue',
        'staff_created_revenue',
        'customer_revenue',
        'cash_revenue',
        'transfer_revenue',
    ];

    // Cast kiểu dữ liệu để số tiền/ngày tháng được dùng ổn định trong controller và view.
    protected $casts = [
        'revenue_date' => 'date',
        'total_orders' => 'integer',
        'total_revenue' => 'decimal:2',
        'staff_created_revenue' => 'decimal:2',
        'customer_revenue' => 'decimal:2',
        'cash_revenue' => 'decimal:2',
        'transfer_revenue' => 'decimal:2',
    ];
}