<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        // Mỗi khách hàng có 1 giỏ hàng
        DB::table('carts')->insert([
            ['user_id' => 4, 'created_at' => now()],
            ['user_id' => 5, 'created_at' => now()],
            ['user_id' => 6, 'created_at' => now()],
        ]);

        // Sản phẩm đang trong giỏ
        DB::table('cart_items')->insert([
            ['cart_id' => 1, 'product_id' => 1,  'quantity' => 2, 'note' => 'Ít đường'],
            ['cart_id' => 1, 'product_id' => 11, 'quantity' => 1, 'note' => null],
            ['cart_id' => 2, 'product_id' => 5,  'quantity' => 1, 'note' => 'Nhiều trân châu'],
            ['cart_id' => 2, 'product_id' => 7,  'quantity' => 1, 'note' => null],
        ]);

        // Topping đã chọn trong giỏ
        DB::table('cart_item_extras')->insert([
            ['cart_item_id' => 1, 'extra_id' => 5], // Cà phê đen → Ít đường
            ['cart_item_id' => 3, 'extra_id' => 1], // Trà sữa trân châu → Trân châu đen
        ]);
    }
}
