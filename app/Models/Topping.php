<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topping extends Model
{
    protected $table = 'extras'; // 👈 dùng bảng extras

    protected $fillable = [
        'name',
        'price',
        'type'
    ];

    public $timestamps = false;

    // scope chỉ lấy topping
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