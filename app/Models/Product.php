<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    public $timestamps = false;

    protected $fillable = [
        'category_id', 'name', 'description', 'price', 'stock', 'image_url', 'status'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

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