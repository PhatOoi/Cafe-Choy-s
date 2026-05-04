<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientWithdrawal extends Model
{
    protected $fillable = [
        'ingredient_id',
        'ingredient_name',
        'quantity',
        'unit',
        'stock_before',
        'stock_after',
        'note',
        'created_by',
    ];

    protected $casts = [
        'quantity'     => 'decimal:2',
        'stock_before' => 'decimal:2',
        'stock_after'  => 'decimal:2',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
