<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id', 'method', 'status', 'amount', 'paid_at', 'ref_code',
    ];

    public function getMethodLabelAttribute()
    {
        return match($this->method) {
            'cod'           => 'Tiền mặt (COD)',
            'cash'          => 'Tiền mặt',
            'bank_transfer' => 'Chuyển khoản',
            'momo'          => 'MoMo',
            'vnpay'         => 'VNPay',
            'zalopay'       => 'ZaloPay',
            default         => $this->method,
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending'  => 'Chờ thanh toán',
            'paid'     => 'Đã thanh toán',
            'failed'   => 'Thất bại',
            'refunded' => 'Đã hoàn tiền',
            default    => $this->status,
        };
    }
}
