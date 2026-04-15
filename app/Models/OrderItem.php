<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'note',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function extras()
    {
        return $this->belongsToMany(
            Extra::class,
            'order_item_extras',
            'order_item_id',
            'extra_id'
        )->withPivot(['extra_name', 'extra_price']);
    }

    public function getSubtotalAttribute()
    {
        $extrasTotal = $this->extras->sum(function ($extra) {
            return (float) ($extra->pivot->extra_price ?? 0);
        });

        return ((float) $this->unit_price + $extrasTotal) * (int) $this->quantity;
    }
}
