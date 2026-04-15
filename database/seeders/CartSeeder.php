<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CartSeeder extends Seeder
{
    // Seed dữ liệu giỏ hàng mẫu cho một vài khách hàng để test luồng cart.
    public function run(): void
    {
        // Mỗi khách hàng mẫu có một giỏ hàng riêng trong bảng carts.
        DB::table('carts')->insert([
            ['user_id' => 4, 'created_at' => now()],
            ['user_id' => 5, 'created_at' => now()],
            ['user_id' => 6, 'created_at' => now()],
        ]);

        // Thêm các món đang nằm trong từng giỏ hàng.
        DB::table('cart_items')->insert([
            ['cart_id' => 1, 'product_id' => 1,  'quantity' => 2, 'note' => 'Ít đường'],
            ['cart_id' => 1, 'product_id' => 11, 'quantity' => 1, 'note' => null],
            ['cart_id' => 2, 'product_id' => 5,  'quantity' => 1, 'note' => 'Nhiều trân châu'],
            ['cart_id' => 2, 'product_id' => 7,  'quantity' => 1, 'note' => null],
        ]);

        // Gắn extra/topping đã chọn cho các dòng cart item tương ứng.
        DB::table('cart_item_extras')->insert([
            ['cart_item_id' => 1, 'extra_id' => 5], // Cà phê đen → Ít đường
            ['cart_item_id' => 3, 'extra_id' => 1], // Trà sữa trân châu → Trân châu đen
        ]);
    }
}
