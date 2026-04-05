<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function extras()
    {
        return $this->belongsToMany(Extra::class, 'cart_item_extras');
    }

}