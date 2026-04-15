<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    public $timestamps = false;
    protected $table    = 'cart_items';
    protected $fillable = ['cart_id','product_id','quantity','note'];

    public function product() { return $this->belongsTo(Product::class); }
    public function extras()  { return $this->belongsToMany(Extra::class,'cart_item_extras','cart_item_id','extra_id'); }

    public function getSubtotalAttribute(): float {
        $extra = $this->extras->sum('price');
        return ($this->product->price + $extra) * $this->quantity;
    }
}
