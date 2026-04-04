<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('shifts')->insert([
            [
                'staff_id'   => 2,
                'start_time' => '2025-07-01 07:00:00',
                'end_time'   => '2025-07-01 15:00:00',
                'note'       => 'Ca sáng',
                'created_at' => '2025-07-01 07:00:00',
            ],
            [
                'staff_id'   => 3,
                'start_time' => '2025-07-01 15:00:00',
                'end_time'   => '2025-07-01 22:00:00',
                'note'       => 'Ca chiều',
                'created_at' => '2025-07-01 15:00:00',
            ],
            [
                'staff_id'   => 2,
                'start_time' => '2025-07-02 07:00:00',
                'end_time'   => '2025-07-02 15:00:00',
                'note'       => 'Ca sáng',
                'created_at' => '2025-07-02 07:00:00',
            ],
        ]);
    }
}
