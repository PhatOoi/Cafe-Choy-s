<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('user_roles')->insert([
            ['name' => 'admin', 'description' => 'Quản lý — toàn quyền hệ thống'],
            ['name' => 'staff', 'description' => 'Nhân viên'],
            ['name' => 'customer', 'description' => 'Khách hàng'],
        ]);
    }
}