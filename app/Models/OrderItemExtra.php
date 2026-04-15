<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemExtra extends Model
{
    // Pivot extra của order item không dùng timestamps tự động.
    public $timestamps = false;

    // Các trường được phép insert hàng loạt khi lưu topping/extra cho món.
    protected $fillable = [
        'order_item_id', 'extra_id', 'extra_name', 'extra_price',
    ];
}
