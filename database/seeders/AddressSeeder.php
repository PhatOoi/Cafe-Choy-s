<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('addresses')->insert([
            [
                'user_id'      => 4,
                'label'        => 'Nhà',
                'address_line' => '12 Nguyễn Trãi',
                'district'     => 'Quận 1',
                'city'         => 'TP.HCM',
                'lat'          => 10.7751,
                'lng'          => 106.6983,
                'is_default'   => true,
            ],
            [
                'user_id'      => 5,
                'label'        => 'Nhà',
                'address_line' => '45 Lê Lợi',
                'district'     => 'Quận 3',
                'city'         => 'TP.HCM',
                'lat'          => 10.7769,
                'lng'          => 106.7009,
                'is_default'   => true,
            ],
            [
                'user_id'      => 5,
                'label'        => 'Công ty',
                'address_line' => '88 Điện Biên Phủ',
                'district'     => 'Quận Bình Thạnh',
                'city'         => 'TP.HCM',
                'lat'          => 10.8006,
                'lng'          => 106.7143,
                'is_default'   => false,
            ],
            [
                'user_id'      => 6,
                'label'        => 'Nhà',
                'address_line' => '9 Cách Mạng Tháng 8',
                'district'     => 'Quận 10',
                'city'         => 'TP.HCM',
                'lat'          => 10.7713,
                'lng'          => 106.6682,
                'is_default'   => true,
            ],
        ]);
    }
}
