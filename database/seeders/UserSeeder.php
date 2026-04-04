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
            [
                'role_id' => 1,
                'name' => 'Admin Coffee Choy',
                'email' => 'admin@coffeechoys.vn',
                'password' => Hash::make('123456'),
                'phone' => '0901000001',
            ],
            [
                'role_id' => 2,
                'name' => 'Nguyễn Văn Nhân',
                'email' => 'nhan@coffeechoys.vn',
                'password' => Hash::make('123456'),
                'phone' => '0901000002',
            ],
            [
                'role_id' => 3,
                'name' => 'Lê Minh Khách',
                'email' => 'khach1@gmail.com',
                'password' => Hash::make('123456'),
                'phone' => '0912000001',
            ],
        ]);
    }
}
