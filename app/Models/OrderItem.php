<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id', 'product_id', 'quantity', 'unit_price', 'note',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function extras()
    {
        return $this->hasMany(OrderItemExtra::class, 'order_item_id');
    }

    public function getSubtotalAttribute()
    {
        $extrasTotal = $this->extras->sum('extra_price');
        return ($this->unit_price + $extrasTotal) * $this->quantity;
    }
}
