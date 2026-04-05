<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    protected $table = 'extras';

    public $timestamps = false;

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