<?php

namespace Database\Seeders;

uuse Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('orders')->insert([
            [
                'user_id' => 3,
                'total_price' => 50000,
                'final_price' => 50000,
                'status' => 'pending'
            ]
        ]);

        DB::table('order_items')->insert([
            [
                'order_id' => 1,
                'product_id' => 1,
                'quantity' => 2,
                'unit_price' => 25000
            ]
        ]);
    }
}