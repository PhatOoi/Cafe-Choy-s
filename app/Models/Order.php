<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'address_id',
        'assigned_staff_id',
        'voucher_id',
        'order_type',
        'status',
        'total_price',
        'discount_amount',
        'shipping_fee',
        'final_price',
        'note',
    ];
        protected $casts = [
        'created_at' => 'datetime',
        'total_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'final_price' => 'decimal:2',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending'    => 'Chờ xác nhận',
            'confirmed'  => 'Đã xác nhận',
            'processing' => 'Đang chuẩn bị',
            'ready'      => 'Sẵn sàng',
            'delivering' => $this->order_type === 'delivery' ? 'Đã giao' : 'Hoàn thành đơn hàng',
            'delivered'  => $this->order_type === 'delivery' ? 'Đã giao' : 'Hoàn thành đơn hàng',
            'failed'     => 'Thất bại',
            'cancelled'  => 'Đã hủy',
            default      => $this->status,
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending'    => 'warning',
            'confirmed'  => 'info',
            'processing' => 'primary',
            'ready'      => 'success',
            'delivering' => 'success',
            'delivered'  => 'success',
            'failed'     => 'danger',
            'cancelled'  => 'secondary',
            default      => 'secondary',
        };
    }

    // Các trạng thái tiếp theo hợp lệ cho nhân viên
    public function getNextStatusesAttribute()
    {
        if ($this->order_type !== 'delivery') {
            return match($this->status) {
                'pending'    => ['confirmed', 'cancelled'],
                'confirmed'  => ['processing', 'cancelled'],
                'processing' => ['ready'],
                'ready'      => ['delivered'],
                default      => [],
            };
        }

        return match($this->status) {
            'pending'    => ['confirmed', 'cancelled'],
            'confirmed'  => ['processing', 'cancelled'],
            'processing' => ['ready'],
            'ready'      => ['delivered'],
            default      => [],
        };
    }
}
