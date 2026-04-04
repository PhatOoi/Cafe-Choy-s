<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            // Admin
            [
                'role_id'    => 1,
                'name'       => 'Admin Coffee Choy',
                'email'      => 'admin@coffeechoys.vn',
                'password'   => Hash::make('Admin@123'),
                'phone'      => '0901000001',
                'is_active'  => true,
                'created_at' => now(),
            ],
            // Staff
            [
                'role_id'    => 2,
                'name'       => 'Nguyễn Văn Nhân',
                'email'      => 'nhan@coffeechoys.vn',
                'password'   => Hash::make('Staff@123'),
                'phone'      => '0901000002',
                'is_active'  => true,
                'created_at' => now(),
            ],
            [
                'role_id'    => 2,
                'name'       => 'Trần Thị Linh',
                'email'      => 'linh@coffeechoys.vn',
                'password'   => Hash::make('Staff@123'),
                'phone'      => '0901000003',
                'is_active'  => true,
                'created_at' => now(),
            ],
            // Customers
            [
                'role_id'    => 3,
                'name'       => 'Lê Minh Khách',
                'email'      => 'khach1@gmail.com',
                'password'   => Hash::make('Customer@123'),
                'phone'      => '0912000001',
                'is_active'  => true,
                'created_at' => now(),
            ],
            [
                'role_id'    => 3,
                'name'       => 'Phạm Thị Hương',
                'email'      => 'huong@gmail.com',
                'password'   => Hash::make('Customer@123'),
                'phone'      => '0912000002',
                'is_active'  => true,
                'created_at' => now(),
            ],
            [
                'role_id'    => 3,
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
