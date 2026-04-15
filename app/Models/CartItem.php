<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    // Cart item được quản lý timestamp thủ công hoặc không cần lưu timestamp.
    public $timestamps = false;

    // Tên bảng chi tiết item trong giỏ hàng.
    protected $table    = 'cart_items';

    // Các trường cho phép mass assignment khi đồng bộ giỏ hàng.
    protected $fillable = ['cart_id','product_id','quantity','note'];

    // Sản phẩm chính của dòng giỏ hàng này.
    public function product() { return $this->belongsTo(Product::class); }

    // Các extra/topping được chọn cho cart item qua bảng pivot.
    public function extras()  { return $this->belongsToMany(Extra::class,'cart_item_extras','cart_item_id','extra_id'); }

    // Accessor tính thành tiền của dòng giỏ hàng, bao gồm giá sản phẩm và extra.
    public function getSubtotalAttribute(): float {
        $extra = $this->extras->sum('price');
        return ($this->product->price + $extra) * $this->quantity;
    }
}
