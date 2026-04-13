<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemExtra extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_item_id', 'extra_id', 'extra_name', 'extra_price',
    ];
}
