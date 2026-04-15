<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Order extends Model
{
    use HasFactory;

    // Khai báo tên bảng thật vì model không dùng timestamps chuẩn Laravel hoàn toàn.
    protected $table = 'orders';

    // Bảng orders đang được quản lý timestamp thủ công trong migration/controller.
    public $timestamps = false;

    // Các cột cho phép mass assignment khi tạo/cập nhật đơn.
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

    // Cast kiểu dữ liệu để khi đọc từ model sẽ tiện xử lý ở view/controller.
    protected $casts = [
        'created_at' => 'datetime',
        'total_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'final_price' => 'decimal:2',
        'updated_at' => 'datetime',
    ];

    // Chủ đơn hàng.
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Nhân viên đang phụ trách/được gán cho đơn.
    public function staff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    // Địa chỉ giao hàng nếu đây là đơn delivery.
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    // Danh sách item thuộc đơn.
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Thông tin thanh toán gắn với đơn.
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // Accessor đổi status code sang nhãn tiếng Việt để hiển thị UI.
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

    // Accessor trả về màu badge theo status để view không phải tự mapping lại.
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
        // Hiện tại cả đơn delivery và in_store đều đi chung một flow trạng thái.
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

    // Khách chỉ được tự hủy khi đơn chưa sang bước chuẩn bị.
    public function canCustomerCancel(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'], true);
    }
}
