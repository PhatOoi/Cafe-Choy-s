<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    // Bảng lưu topping, đường, đá và các option thêm của món.
    protected $table = 'extras';

    // Không dùng timestamps tự động cho extras.
    public $timestamps = false;

    // Các cột có thể gán hàng loạt khi seed hoặc quản trị option.
    protected $fillable = [
        'name',
        'price',
        'type'
    ];

    // Scopes để phân loại topping, sugar, ice
    public function scopeTopping($query)
    {
        return $query->where('type', 'topping');
    }

    public function scopeSugar($query)
    {
        return $query->where('type', 'sugar');
    }

    public function scopeIce($query)
    {
        return $query->where('type', 'ice');
    }
}