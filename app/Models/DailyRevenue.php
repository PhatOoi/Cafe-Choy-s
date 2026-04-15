<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyRevenue extends Model
{
    protected $table = 'daily_revenues';

    protected $fillable = [
        'revenue_date',
        'total_orders',
        'total_revenue',
        'staff_created_revenue',
        'customer_revenue',
        'cash_revenue',
        'transfer_revenue',
    ];

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