<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // Tên bảng sản phẩm trong database.
    protected $table = 'products';

    // Bảng này không dùng created_at/updated_at tự động.
    public $timestamps = false;

    // Các cột cho phép mass assignment khi admin tạo/sửa sản phẩm.
    protected $fillable = [
        'category_id', 'name', 'description', 'price', 'stock', 'image_url', 'status'
    ];

    // Danh mục mà sản phẩm thuộc về.
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Các extra có thể áp dụng cho sản phẩm nếu hệ thống dùng bảng pivot product_extras.
    public function extras()
    {
        return $this->belongsToMany(
            Extra::class,
            'product_extras',
            'product_id',
            'extra_id'
        );
    }
}