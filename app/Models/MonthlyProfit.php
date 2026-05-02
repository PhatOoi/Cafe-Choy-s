<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyProfit extends Model
{
    protected $table = 'monthly_profits';

    protected $fillable = [
        'month_start',
        'ingredient_cost',
        'electricity_cost',
        'water_cost',
        'service_cost',
        'depreciation_cost',
        'rent_cost',
    ];

    protected $casts = [
        'month_start' => 'date',
        'ingredient_cost' => 'decimal:2',
        'electricity_cost' => 'decimal:2',
        'water_cost' => 'decimal:2',
        'service_cost' => 'decimal:2',
        'depreciation_cost' => 'decimal:2',
        'rent_cost' => 'decimal:2',
    ];
}
