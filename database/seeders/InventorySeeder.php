<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('inventory')->insert([
            ['name' => 'Cà phê hạt Arabica',   'quantity' => 15.5, 'unit' => 'kg',  'min_stock' => 5],
            ['name' => 'Sữa tươi không đường', 'quantity' => 20,   'unit' => 'lít', 'min_stock' => 5],
            ['name' => 'Sữa đặc',              'quantity' => 10,   'unit' => 'hộp', 'min_stock' => 3],
            ['name' => 'Trà đen',              'quantity' => 5,    'unit' => 'kg',  'min_stock' => 1],
            ['name' => 'Bột Matcha',           'quantity' => 2,    'unit' => 'kg',  'min_stock' => 0.5],
            ['name' => 'Trân Châu Đen',        'quantity' => 3,    'unit' => 'kg',  'min_stock' => 1],
            ['name' => 'Đường',                'quantity' => 10,   'unit' => 'kg',  'min_stock' => 2],
            ['name' => 'Whipping Cream',       'quantity' => 5,    'unit' => 'lít', 'min_stock' => 1],
            ['name' => 'Ly nhựa 500ml',        'quantity' => 500,  'unit' => 'cái', 'min_stock' => 100],
            ['name' => 'Ống hút',              'quantity' => 1000, 'unit' => 'cái', 'min_stock' => 200],
        ]);

        DB::table('inventory_logs')->insert([
            ['inventory_id' => 1, 'staff_id' => 2, 'type' => 'import', 'quantity' =>  5,    'note' => 'Nhập hàng tuần',        'created_at' => '2025-07-01 07:30:00'],
            ['inventory_id' => 2, 'staff_id' => 2, 'type' => 'import', 'quantity' =>  20,   'note' => 'Nhập sữa tươi',         'created_at' => '2025-07-01 07:30:00'],
            ['inventory_id' => 6, 'staff_id' => 2, 'type' => 'import', 'quantity' =>  3,    'note' => 'Nhập trân châu',        'created_at' => '2025-07-01 07:30:00'],
            ['inventory_id' => 9, 'staff_id' => 2, 'type' => 'import', 'quantity' =>  500,  'note' => 'Nhập ly nhựa',          'created_at' => '2025-07-01 07:30:00'],
            ['inventory_id' => 1, 'staff_id' => 2, 'type' => 'export', 'quantity' => -0.5,  'note' => 'Dùng cho ca sáng 01/07','created_at' => '2025-07-01 15:00:00'],
            ['inventory_id' => 2, 'staff_id' => 2, 'type' => 'export', 'quantity' => -2,    'note' => 'Dùng cho ca sáng 01/07','created_at' => '2025-07-01 15:00:00'],
        ]);
    }
}
