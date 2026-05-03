<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayrollAprilSampleSeeder extends Seeder
{
    // Seed dữ liệu mẫu bảng lương tháng 04/2026 cho 1 full-time và 1 part-time.
    public function run(): void
    {
        $adminId = DB::table('users')->where('email', 'choyscaffe@gmail.com')->value('id');
        $fullTimeId = DB::table('users')->where('email', 'nhan@coffeechoys.vn')->value('id');
        $partTimeId = DB::table('users')->where('email', 'linh@coffeechoys.vn')->value('id');

        if (!$adminId || !$fullTimeId || !$partTimeId) {
            return;
        }

        $monthStart = '2026-04-01';
        $monthEnd = '2026-04-30';

        // Dọn dữ liệu mẫu cũ trong tháng 04 để seeder có thể chạy lặp lại an toàn.
        DB::table('work_schedule_registrations')
            ->whereIn('staff_id', [$fullTimeId, $partTimeId])
            ->whereBetween('work_date', [$monthStart, $monthEnd])
            ->delete();

        DB::table('overtimes')
            ->whereIn('staff_id', [$fullTimeId, $partTimeId])
            ->whereBetween('overtime_date', [$monthStart, $monthEnd])
            ->delete();

        $fullTimeDates = [
            '2026-04-02',
            '2026-04-03',
            '2026-04-06',
            '2026-04-10',
            '2026-04-14',
            '2026-04-18',
            '2026-04-22',
            '2026-04-28',
        ];

        $partTimeDates = [
            '2026-04-01',
            '2026-04-05',
            '2026-04-09',
            '2026-04-13',
            '2026-04-17',
            '2026-04-21',
            '2026-04-25',
            '2026-04-29',
        ];

        $scheduleRows = [];

        foreach ($fullTimeDates as $date) {
            $scheduleRows[] = [
                'staff_id' => $fullTimeId,
                'employment_type' => 'full_time',
                'work_date' => $date,
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'shift_label' => 'Ca full-time 08:00 - 17:00',
                'note' => null,
                'status' => 'closed',
                'approved_by' => $adminId,
                'approved_at' => Carbon::parse($date . ' 07:30:00'),
                'closed_by' => $adminId,
                'closed_at' => Carbon::parse($date . ' 22:10:00'),
                'created_at' => Carbon::parse($date . ' 06:50:00'),
            ];
        }

        foreach ($partTimeDates as $date) {
            $scheduleRows[] = [
                'staff_id' => $partTimeId,
                'employment_type' => 'part_time',
                'work_date' => $date,
                'start_time' => '17:00:00',
                'end_time' => '22:00:00',
                'shift_label' => 'Ca part-time 17:00 - 22:00',
                'note' => null,
                'status' => 'approved',
                'approved_by' => $adminId,
                'approved_at' => Carbon::parse($date . ' 16:00:00'),
                'closed_by' => null,
                'closed_at' => null,
                'created_at' => Carbon::parse($date . ' 15:10:00'),
            ];
        }

        DB::table('work_schedule_registrations')->insert($scheduleRows);

        DB::table('overtimes')->insert([
            [
                'staff_id' => $fullTimeId,
                'overtime_date' => '2026-04-11',
                'hours' => 2.00,
                'status' => 'approved',
                'notes' => 'Hỗ trợ kiểm kê cuối tuần',
                'created_at' => Carbon::parse('2026-04-11 18:00:00'),
                'updated_at' => Carbon::parse('2026-04-11 19:00:00'),
            ],
            [
                'staff_id' => $partTimeId,
                'overtime_date' => '2026-04-24',
                'hours' => 1.50,
                'status' => 'approved',
                'notes' => 'Hỗ trợ sự kiện nội bộ',
                'created_at' => Carbon::parse('2026-04-24 22:10:00'),
                'updated_at' => Carbon::parse('2026-04-24 22:45:00'),
            ],
        ]);
    }
}
