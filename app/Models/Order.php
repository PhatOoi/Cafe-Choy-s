<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'address_id', 'assigned_staff_id', 'voucher_id',
        'order_type', 'status', 'total_price', 'discount_amount',
        'shipping_fee', 'final_price', 'note',
    ];

    protected $dates = ['created_at'];

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
            'processing' => 'Đang pha chế',
            'ready'      => 'Sẵn sàng',
            'delivering' => 'Đang giao',
            'delivered'  => 'Đã giao',
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
            'delivering' => 'info',
            'delivered'  => 'success',
            'failed'     => 'danger',
            'cancelled'  => 'secondary',
            default      => 'secondary',
        };
    }

    // Các trạng thái tiếp theo hợp lệ cho nhân viên
    public function getNextStatusesAttribute()
    {
        return match($this->status) {
            'pending'    => ['confirmed', 'cancelled'],
            'confirmed'  => ['processing', 'cancelled'],
            'processing' => ['ready'],
            'ready'      => ['delivering', 'delivered'],
            'delivering' => ['delivered', 'failed'],
            default      => [],
        };
    }
}
