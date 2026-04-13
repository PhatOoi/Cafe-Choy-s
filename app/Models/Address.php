<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'label', 'address_line', 'district', 'city', 'lat', 'lng', 'is_default',
    ];

    public function getFullAddressAttribute()
    {
        return trim($this->address_line . ', ' . $this->district . ', ' . $this->city, ', ');
    }
}
