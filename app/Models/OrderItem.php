<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    // Tên bảng lưu từng món thuộc đơn hàng.
    protected $table = 'order_items';

    // Order item không dùng timestamps tự động.
    public $timestamps = false;

    // Các cột cho phép mass assignment khi tạo chi tiết đơn.
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'note',
    ];

    // Cast giá tiền sang decimal để tính tổng ổn định.
    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    // Đơn hàng cha mà item này thuộc về.
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Sản phẩm gốc của dòng đơn hàng.
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Các extra/topping đã áp cho item này, đọc qua bảng order_item_extras.
    public function extras()
    {
        return $this->belongsToMany(
            Extra::class,
            'order_item_extras',
            'order_item_id',
            'extra_id'
        )->withPivot(['extra_name', 'extra_price']);
    }

    // Accessor tính thành tiền của một dòng đơn, gồm giá món và các extra đi kèm.
    public function getSubtotalAttribute()
    {
        $extrasTotal = $this->extras->sum(function ($extra) {
            return (float) ($extra->pivot->extra_price ?? 0);
        });

        return ((float) $this->unit_price + $extrasTotal) * (int) $this->quantity;
    }
}
