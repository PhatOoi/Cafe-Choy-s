<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('products')->insert([
            ['category_id' => 1, 'name' => 'Cà Phê Đen', 'price' => 25000, 'stock' => 100],
            ['category_id' => 1, 'name' => 'Cà Phê Sữa', 'price' => 30000, 'stock' => 100],
            ['category_id' => 2, 'name' => 'Trà Sữa Trân Châu', 'price' => 45000, 'stock' => 50],
            ['category_id' => 3, 'name' => 'Đá Xay Chocolate', 'price' => 50000, 'stock' => 50],
            ['category_id' => 4, 'name' => 'Nước Ép Cam', 'price' => 35000, 'stock' => 40],
            ['category_id' => 5, 'name' => 'Bánh Sừng Bò', 'price' => 35000, 'stock' => 20],
        ]);
    }
}
