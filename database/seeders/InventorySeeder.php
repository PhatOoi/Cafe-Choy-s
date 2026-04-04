<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('inventory')->insert([
            ['name' => 'Cà phê hạt', 'quantity' => 10, 'unit' => 'kg'],
            ['name' => 'Sữa tươi', 'quantity' => 20, 'unit' => 'lít'],
            ['name' => 'Đường', 'quantity' => 15, 'unit' => 'kg'],
        ]);
    }
}
