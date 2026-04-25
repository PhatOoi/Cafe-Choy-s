<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration bỏ unique slot theo employment_type để part-time có thể có 2 người cùng slot/ngày.
return new class extends Migration
{
    // Bỏ unique employment_type + work_date + slot, giữ lại unique staff + work_date từ migration sau.
    public function up(): void
    {
        Schema::table('work_schedule_registrations', function (Blueprint $table) {
            $table->dropUnique('uq_work_schedule_type_slot');
        });
    }

    // Rollback: khôi phục unique slot cũ nếu cần.
    public function down(): void
    {
        Schema::table('work_schedule_registrations', function (Blueprint $table) {
            $table->unique(
                ['employment_type', 'work_date', 'start_time', 'end_time'],
                'uq_work_schedule_type_slot'
            );
        });
    }
};
