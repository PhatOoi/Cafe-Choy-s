<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    // Seed các tài khoản mẫu cho admin, staff và customer để test toàn bộ hệ thống.
    public function run(): void
    {
        DB::table('users')->insert([
            // Admin
            [
                'role_id'    => 1,
                'employment_type' => null,
                'name'       => 'Admin Coffee Choy',
                'email'      => 'choyscaffe@gmail.com',
                'password'   => Hash::make('Admin@123'),
                'phone'      => '0901000001',
                'is_active'  => true,
                'created_at' => now(),
            ],
            // Staff vận hành nội bộ.
            [
                'role_id'    => 2,
                'employment_type' => 'full_time',
                'name'       => 'Nguyễn Văn Nhân',
                'email'      => 'nhan@coffeechoys.vn',
                'password'   => Hash::make('Staff@123'),
                'phone'      => '0901000002',
                'is_active'  => true,
                'created_at' => now(),
            ],
            [
                'role_id'    => 2,
                'employment_type' => 'part_time',
                'name'       => 'Trần Thị Linh',
                'email'      => 'linh@coffeechoys.vn',
                'password'   => Hash::make('Staff@123'),
                'phone'      => '0901000003',
                'is_active'  => true,
                'created_at' => now(),
            ],
            // Các tài khoản khách hàng mẫu dùng cho cart, voucher và order history.
            [
                'role_id'    => 3,
                'employment_type' => null,
                'name'       => 'Lê Minh Khách',
                'email'      => 'khach1@gmail.com',
                'password'   => Hash::make('Customer@123'),
                'phone'      => '0912000001',
                'is_active'  => true,
                'created_at' => now(),
            ],
            [
                'role_id'    => 3,
                'employment_type' => null,
                'name'       => 'Phạm Thị Hương',
                'email'      => 'huong@gmail.com',
                'password'   => Hash::make('Customer@123'),
                'phone'      => '0912000002',
                'is_active'  => true,
                'created_at' => now(),
            ],
            [
                'role_id'    => 3,
                'employment_type' => null,
                'name'       => 'Võ Quốc Bảo',
                'email'      => 'bao@gmail.com',
                'password'   => Hash::make('Customer@123'),
                'phone'      => '0912000003',
                'is_active'  => true,
                'created_at' => now(),
            ],
        ]);
    }
}
