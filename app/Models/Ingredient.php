<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'unit',
        'stock_quantity',
        'received_date',
        'minimum_quantity',
        'manufacture_date',
        'expiry_date',
        'lot_number',
        'image_url',
        'is_active',
        'note',
        'unit_price',
        'total_amount',
    ];

    protected $casts = [
        'stock_quantity' => 'decimal:2',
        'received_date' => 'date',
        'minimum_quantity' => 'decimal:2',
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];
}
