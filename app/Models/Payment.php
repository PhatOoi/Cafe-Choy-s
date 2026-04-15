<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    // Payment được quản lý timestamp thủ công theo logic đơn hàng.
    public $timestamps = false;

    // Các trường cho phép tạo/cập nhật payment bằng mass assignment.
    protected $fillable = [
        'order_id', 'method', 'status', 'amount', 'paid_at', 'ref_code',
    ];

    // Accessor đổi payment method sang nhãn dễ hiểu cho giao diện.
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

    // Accessor đổi trạng thái thanh toán sang nhãn tiếng Việt.
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
