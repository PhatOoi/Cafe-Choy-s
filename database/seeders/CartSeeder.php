<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('carts')->insert([
            ['user_id' => 3],
        ]);

        DB::table('cart_items')->insert([
            [
                'cart_id' => 1,
                'product_id' => 1,
                'quantity' => 2,
                'note' => 'Ít đường'
            ]
        ]);
    }
}
