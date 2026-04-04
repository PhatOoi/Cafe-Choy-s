<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExtraSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('extras')->insert([
            ['name' => 'Trân Châu Đen', 'price' => 5000],
            ['name' => 'Thạch', 'price' => 5000],
            ['name' => 'Whipping Cream', 'price' => 10000],
            ['name' => 'Ít Đường', 'price' => 0],
        ]);
    }
}
