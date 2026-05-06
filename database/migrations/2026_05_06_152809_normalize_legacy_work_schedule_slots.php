<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Full-time legacy: 08:00-17:00 => 08:00-16:00
        DB::table('work_schedule_registrations')
            ->where('employment_type', 'full_time')
            ->where('start_time', '08:00:00')
            ->where('end_time', '17:00:00')
            ->update([
                'end_time' => '16:00:00',
                'shift_label' => '8h-16h',
            ]);

        // Part-time legacy: 17:00-22:00 => 16:00-20:00
        DB::table('work_schedule_registrations')
            ->where('employment_type', 'part_time')
            ->where('start_time', '17:00:00')
            ->where('end_time', '22:00:00')
            ->update([
                'start_time' => '16:00:00',
                'end_time' => '20:00:00',
                'shift_label' => '16h-20h',
            ]);
    }

    public function down(): void
    {
        // Irreversible data migration: old legacy ranges are intentionally normalized.
    }
};
