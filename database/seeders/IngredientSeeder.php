<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ingredient;

class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name'             => 'Hat cafe',
                'brand'            => 'Roastery',
                'unit'             => 'kg',
                'stock_quantity'   => 50,
                'unit_price'       => 150000,
                'total_amount'     => 7500000,
                'lot_number'       => 'C001',
                'received_date'    => '2026-04-30',
                'manufacture_date' => '2026-04-10',
                'expiry_date'      => '2026-05-10',
                'is_active'        => true,
                'note'             => null,
            ],
            [
                'name'             => 'Matcha premium',
                'brand'            => 'Fuji',
                'unit'             => 'kg',
                'stock_quantity'   => 5,
                'unit_price'       => 2800000,
                'total_amount'     => 14000000,
                'lot_number'       => 'M001',
                'received_date'    => '2026-04-30',
                'manufacture_date' => '2026-04-10',
                'expiry_date'      => '2026-10-10',
                'is_active'        => true,
                'note'             => null,
            ],
            [
                'name'             => 'Sirup đâu',
                'brand'            => 'Monin',
                'unit'             => 'chai',
                'stock_quantity'   => 10,
                'unit_price'       => 230000,
                'total_amount'     => 2300000,
                'lot_number'       => 'SRD001',
                'received_date'    => '2026-04-30',
                'manufacture_date' => '2026-04-10',
                'expiry_date'      => '2026-10-10',
                'is_active'        => true,
                'note'             => null,
            ],
            [
                'name'             => 'Trà đen',
                'brand'            => 'Phúc Long',
                'unit'             => 'kg',
                'stock_quantity'   => 20,
                'unit_price'       => 240000,
                'total_amount'     => 4800000,
                'lot_number'       => 'TD001',
                'received_date'    => '2026-04-30',
                'manufacture_date' => '2026-04-10',
                'expiry_date'      => '2026-07-07',
                'is_active'        => true,
                'note'             => null,
            ],
            [
                'name'             => 'Trà lài',
                'brand'            => 'Cozy',
                'unit'             => 'kg',
                'stock_quantity'   => 20,
                'unit_price'       => 150000,
                'total_amount'     => 3000000,
                'lot_number'       => 'TL001',
                'received_date'    => '2026-04-30',
                'manufacture_date' => '2026-04-10',
                'expiry_date'      => '2026-07-07',
                'is_active'        => true,
                'note'             => null,
            ],
        ];

        foreach ($items as $item) {
            Ingredient::firstOrCreate(['lot_number' => $item['lot_number']], $item);
        }
    }
}
