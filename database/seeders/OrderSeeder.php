<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    // Seed các đơn mẫu gồm delivery, tại quán, payment và sử dụng voucher.
    public function run(): void
    {
        // Tạo các đơn mẫu với nhiều trạng thái khác nhau để test dashboard và lịch sử đơn.
        DB::table('orders')->insert([
            [
                'user_id'           => 4,
                'address_id'        => 1,
                'assigned_staff_id' => 2,
                'voucher_id'        => 1,
                'order_type'        => 'delivery',
                'status'            => 'delivered',
                'total_price'       => 80000,
                'discount_amount'   => 8000,
                'shipping_fee'      => 15000,
                'final_price'       => 87000,
                'note'              => null,
                'created_at'        => '2025-07-01 09:00:00',
            ],
            [
                'user_id'           => 5,
                'address_id'        => 2,
                'assigned_staff_id' => 2,
                'voucher_id'        => null,
                'order_type'        => 'delivery',
                'status'            => 'delivering',
                'total_price'       => 95000,
                'discount_amount'   => 0,
                'shipping_fee'      => 15000,
                'final_price'       => 110000,
                'note'              => 'Ít đá',
                'created_at'        => '2025-07-01 10:00:00',
            ],
            [
                'user_id'           => 6,
                'address_id'        => 4,
                'assigned_staff_id' => 3,
                'voucher_id'        => 2,
                'order_type'        => 'delivery',
                'status'            => 'confirmed',
                'total_price'       => 250000,
                'discount_amount'   => 50000,
                'shipping_fee'      => 15000,
                'final_price'       => 215000,
                'note'              => 'Giao trước 10h',
                'created_at'        => '2025-07-01 11:00:00',
            ],
            [
                'user_id'           => 4,
                'address_id'        => null,
                'assigned_staff_id' => 2,
                'voucher_id'        => null,
                'order_type'        => 'in_store',
                'status'            => 'delivered',
                'total_price'       => 55000,
                'discount_amount'   => 0,
                'shipping_fee'      => 0,
                'final_price'       => 55000,
                'note'              => 'Mua tại quán',
                'created_at'        => '2025-07-01 10:30:00',
            ],
        ]);

        // Thêm các dòng sản phẩm tương ứng cho từng đơn đã tạo phía trên.
        DB::table('order_items')->insert([
            ['order_id' => 1, 'product_id' => 1,  'quantity' => 2, 'unit_price' => 25000, 'note' => 'Ít đường'],
            ['order_id' => 1, 'product_id' => 11, 'quantity' => 1, 'unit_price' => 35000, 'note' => null],
            ['order_id' => 2, 'product_id' => 5,  'quantity' => 1, 'unit_price' => 45000, 'note' => 'Nhiều trân châu'],
            ['order_id' => 2, 'product_id' => 6,  'quantity' => 1, 'unit_price' => 50000, 'note' => null],
            ['order_id' => 3, 'product_id' => 3,  'quantity' => 2, 'unit_price' => 45000, 'note' => null],
            ['order_id' => 3, 'product_id' => 7,  'quantity' => 2, 'unit_price' => 55000, 'note' => null],
            ['order_id' => 3, 'product_id' => 11, 'quantity' => 2, 'unit_price' => 35000, 'note' => null],
            ['order_id' => 4, 'product_id' => 2,  'quantity' => 1, 'unit_price' => 30000, 'note' => null],
            ['order_id' => 4, 'product_id' => 12, 'quantity' => 1, 'unit_price' => 25000, 'note' => null],
        ]);

        // Lưu snapshot topping/extra của từng dòng đơn khi có chọn thêm.
        DB::table('order_item_extras')->insert([
            ['order_item_id' => 1, 'extra_id' => 5, 'extra_name' => 'Ít Đường',      'extra_price' => 0],
            ['order_item_id' => 3, 'extra_id' => 1, 'extra_name' => 'Trân Châu Đen', 'extra_price' => 5000],
        ]);

        // Gắn dữ liệu thanh toán mẫu để test nhiều phương thức và nhiều trạng thái payment.
        DB::table('payments')->insert([
            ['order_id' => 1, 'method' => 'momo',          'status' => 'paid',    'amount' => 87000,  'paid_at' => '2025-07-01 09:15:00', 'ref_code' => 'MM20250701001'],
            ['order_id' => 2, 'method' => 'bank_transfer', 'status' => 'pending', 'amount' => 110000, 'paid_at' => null,                  'ref_code' => null],
            ['order_id' => 3, 'method' => 'cod',           'status' => 'pending', 'amount' => 215000, 'paid_at' => null,                  'ref_code' => null],
            ['order_id' => 4, 'method' => 'cash',          'status' => 'paid',    'amount' => 55000,  'paid_at' => '2025-07-01 10:30:00', 'ref_code' => null],
        ]);

        // Đánh dấu các voucher thực sự đã được dùng cho đúng order tương ứng.
        DB::table('user_vouchers')
            ->where('user_id', 4)->where('voucher_id', 1)
            ->update(['used_at' => '2025-07-01 09:10:00', 'order_id' => 1]);

        DB::table('user_vouchers')
            ->where('user_id', 6)->where('voucher_id', 2)
            ->update(['used_at' => '2025-07-01 11:00:00', 'order_id' => 3]);
    }
}
