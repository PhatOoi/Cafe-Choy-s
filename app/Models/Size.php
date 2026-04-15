<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    // Tên bảng kích cỡ sản phẩm.
    protected $table = 'sizes';

    // Size không dùng timestamps tự động.
    public $timestamps = false;

    // Các cột admin/seeder có thể gán hàng loạt.
    protected $fillable = [
        'name',
        'extra_price'
    ];
}