<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // Tên bảng danh mục sản phẩm.
    protected $table = 'categories';

    // Bảng category không dùng timestamps tự động.
    public $timestamps = false;

    // Các cột admin có thể tạo/sửa hàng loạt cho category.
    protected $fillable = [
        'name', 'slug', 'description', 'sort_order'
    ];

    // Một category có nhiều sản phẩm con.
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}