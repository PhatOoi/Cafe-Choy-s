<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SizeSeeder extends Seeder
{
    // Seed các kích cỡ chuẩn và phụ thu tương ứng cho sản phẩm đồ uống.
    public function run(): void
    {
        // Chỉ insert khi bảng sizes còn trống để tránh dữ liệu trùng lặp.
        if (DB::table('sizes')->count() === 0) {
            DB::table('sizes')->insert([
                ['name' => 'S', 'extra_price' => 0],
                ['name' => 'M', 'extra_price' => 10000],
                ['name' => 'L', 'extra_price' => 15000],
            ]);
        }
    }
}
