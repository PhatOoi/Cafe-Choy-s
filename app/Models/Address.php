<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    // Bảng địa chỉ không dùng created_at/updated_at tự động.
    public $timestamps = false;

    // Các cột cho phép gán hàng loạt khi tạo/sửa địa chỉ giao hàng.
    protected $fillable = [
        'user_id', 'label', 'address_line', 'district', 'city', 'lat', 'lng', 'is_default',
    ];

    // Accessor ghép các phần địa chỉ thành chuỗi đầy đủ để hiển thị ở view.
    public function getFullAddressAttribute()
    {
        return trim($this->address_line . ', ' . $this->district . ', ' . $this->city, ', ');
    }
}
