<?php

namespace Database\Seeders;

uuse Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('vouchers')->insert([
            [
                'code' => 'WELCOME10',
                'discount' => 10,
                'type' => 'percent',
                'min_order_value' => 50000,
                'expired_at' => now()->addMonths(6)
            ],
            [
                'code' => 'SALE50K',
                'discount' => 50000,
                'type' => 'amount',
                'min_order_value' => 200000,
                'expired_at' => now()->addMonths(3)
            ],
        ]);
    }
}
