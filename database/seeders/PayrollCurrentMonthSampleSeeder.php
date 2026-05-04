<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayrollCurrentMonthSampleSeeder extends Seeder
{
    // Seed dữ liệu mẫu lịch làm + tăng ca cho tháng hiện tại của 2 tài khoản staff mẫu.
    public function run(): void
    {
        $adminId = DB::table('users')->where('email', 'choyscaffe@gmail.com')->value('id');
        $fullTimeId = DB::table('users')->where('email', 'nhan@coffeechoys.vn')->value('id');
        $partTimeId = DB::table('users')->where('email', 'linh@coffeechoys.vn')->value('id');

        if (!$adminId || !$fullTimeId || !$partTimeId) {
            return;
        }

        $monthStart = now()->copy()->startOfMonth();
        $monthEnd = now()->copy()->endOfMonth();
        $today = now()->copy()->startOfDay();

        $fullTimeDates = [];
        $partTimeDates = [];

        // Lấy mẫu ngày làm trong tháng: full-time vào ngày chẵn, part-time vào ngày lẻ, bỏ Chủ nhật.
        for ($cursor = $monthStart->copy(); $cursor->lte($monthEnd); $cursor->addDay()) {
            if ($cursor->isSunday()) {
                continue;
            }

            if ($cursor->day % 2 === 0) {
                $fullTimeDates[] = $cursor->copy();
            } else {
                $partTimeDates[] = $cursor->copy();
            }
        }

        foreach ($fullTimeDates as $workDate) {
            $isPastOrToday = $workDate->lte($today);
            $status = $isPastOrToday ? 'closed' : 'pending';

            DB::table('work_schedule_registrations')->updateOrInsert(
                [
                    'staff_id' => $fullTimeId,
                    'work_date' => $workDate->toDateString(),
                ],
                [
                    'start_time' => '08:00:00',
                    'end_time' => '17:00:00',
                    'employment_type' => 'full_time',
                    'shift_label' => 'Ca full-time 08:00 - 17:00',
                    'note' => '[SEED_PAYROLL_CURRENT_MONTH] Ca mẫu full-time',
                    'status' => $status,
                    'approved_by' => $isPastOrToday ? $adminId : null,
                    'approved_at' => $isPastOrToday ? Carbon::parse($workDate->toDateString() . ' 07:30:00') : null,
                    'closed_by' => $status === 'closed' ? $adminId : null,
                    'closed_at' => $status === 'closed' ? Carbon::parse($workDate->toDateString() . ' 22:00:00') : null,
                    'created_at' => Carbon::parse($workDate->toDateString() . ' 06:50:00'),
                ]
            );
        }

        foreach ($partTimeDates as $workDate) {
            $isPast = $workDate->lt($today);
            $status = $isPast ? 'approved' : 'pending';

            DB::table('work_schedule_registrations')->updateOrInsert(
                [
                    'staff_id' => $partTimeId,
                    'work_date' => $workDate->toDateString(),
                ],
                [
                    'start_time' => '17:00:00',
                    'end_time' => '22:00:00',
                    'employment_type' => 'part_time',
                    'shift_label' => 'Ca part-time 17:00 - 22:00',
                    'note' => '[SEED_PAYROLL_CURRENT_MONTH] Ca mẫu part-time',
                    'status' => $status,
                    'approved_by' => $isPast ? $adminId : null,
                    'approved_at' => $isPast ? Carbon::parse($workDate->toDateString() . ' 16:00:00') : null,
                    'closed_by' => null,
                    'closed_at' => null,
                    'created_at' => Carbon::parse($workDate->toDateString() . ' 15:10:00'),
                ]
            );
        }

        // Seed tăng ca mẫu ở các ngày đã qua để xuất hiện trong payroll tháng hiện tại.
        $overtimeCandidateDates = [
            $monthStart->copy()->addDays(2),
            $monthStart->copy()->addDays(10),
        ];

        foreach ($overtimeCandidateDates as $index => $date) {
            if ($date->gte($today) || $date->gt($monthEnd)) {
                continue;
            }

            DB::table('overtimes')->updateOrInsert(
                [
                    'staff_id' => $index === 0 ? $fullTimeId : $partTimeId,
                    'overtime_date' => $date->toDateString(),
                ],
                [
                    'hours' => $index === 0 ? 2.0 : 1.5,
                    'status' => 'approved',
                    'notes' => '[SEED_PAYROLL_CURRENT_MONTH] Tăng ca mẫu',
                    'created_at' => Carbon::parse($date->toDateString() . ' 21:30:00'),
                    'updated_at' => Carbon::parse($date->toDateString() . ' 22:00:00'),
                ]
            );
        }
    }
}
