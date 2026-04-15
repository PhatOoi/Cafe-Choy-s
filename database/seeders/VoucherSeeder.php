<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VoucherSeeder extends Seeder
{
    // Seed các voucher mẫu và gán sẵn một số voucher cho user demo.
    public function run(): void
    {
        // Tạo các voucher với cả 2 kiểu giảm giá: phần trăm và số tiền cố định.
        DB::table('vouchers')->insert([
            [
                'code'            => 'WELCOME10',
                'discount'        => 10,
                'type'            => 'percent',
                'min_order_value' => 50000,
                'max_discount'    => 20000,
                'usage_limit'     => 100,
                'used_count'      => 0,
                'expired_at'      => '2025-12-31 23:59:59',
                'is_active'       => true,
            ],
            [
                'code'            => 'CHOYS50K',
                'discount'        => 50000,
                'type'            => 'amount',
                'min_order_value' => 200000,
                'max_discount'    => null,
                'usage_limit'     => 50,
                'used_count'      => 0,
                'expired_at'      => '2025-09-30 23:59:59',
                'is_active'       => true,
            ],
            [
                'code'            => 'SUMMER20',
                'discount'        => 20,
                'type'            => 'percent',
                'min_order_value' => 100000,
                'max_discount'    => 40000,
                'usage_limit'     => 200,
                'used_count'      => 0,
                'expired_at'      => '2025-08-31 23:59:59',
                'is_active'       => true,
            ],
        ]);

        // Gán voucher cho một vài user mẫu để test luồng nhận và sử dụng voucher.
        DB::table('user_vouchers')->insert([
            ['user_id' => 4, 'voucher_id' => 1, 'used_at' => null, 'order_id' => null],
            ['user_id' => 4, 'voucher_id' => 2, 'used_at' => null, 'order_id' => null],
            ['user_id' => 5, 'voucher_id' => 1, 'used_at' => null, 'order_id' => null],
            ['user_id' => 5, 'voucher_id' => 3, 'used_at' => null, 'order_id' => null],
            ['user_id' => 6, 'voucher_id' => 1, 'used_at' => null, 'order_id' => null],
        ]);
    }
}
