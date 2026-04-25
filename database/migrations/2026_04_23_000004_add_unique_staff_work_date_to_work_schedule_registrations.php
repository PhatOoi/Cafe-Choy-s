<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Migration đảm bảo mỗi nhân viên chỉ có tối đa 1 ca trong một ngày.
return new class extends Migration
{
    // Xóa dữ liệu trùng staff+ngày nếu có và thêm unique constraint ở mức DB.
    public function up(): void
    {
        $duplicateKeys = DB::table('work_schedule_registrations')
            ->select('staff_id', 'work_date', DB::raw('COUNT(*) as total'))
            ->groupBy('staff_id', 'work_date')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        // Giữ bản ghi đầu tiên, xóa các bản ghi dư để tạo unique key không bị lỗi.
        foreach ($duplicateKeys as $duplicateKey) {
            $rows = DB::table('work_schedule_registrations')
                ->where('staff_id', $duplicateKey->staff_id)
                ->whereDate('work_date', $duplicateKey->work_date)
                ->orderBy('id')
                ->pluck('id')
                ->all();

            if (count($rows) > 1) {
                $idsToDelete = array_slice($rows, 1);

                DB::table('work_schedule_registrations')
                    ->whereIn('id', $idsToDelete)
                    ->delete();
            }
        }

        Schema::table('work_schedule_registrations', function (Blueprint $table) {
            $table->unique(['staff_id', 'work_date'], 'uq_work_schedule_staff_day');
        });
    }

    // Rollback ràng buộc unique staff+day.
    public function down(): void
    {
        Schema::table('work_schedule_registrations', function (Blueprint $table) {
            $table->dropUnique('uq_work_schedule_staff_day');
        });
    }
};
