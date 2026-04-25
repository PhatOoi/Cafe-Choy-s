<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration đổi ràng buộc unique để mỗi slot chỉ có 1 người trong cùng nhóm employment_type.
return new class extends Migration
{
    // Bỏ unique theo staff và thay bằng unique theo loại nhân viên + ngày + khung giờ.
    public function up(): void
    {
        Schema::table('work_schedule_registrations', function (Blueprint $table) {
            $table->dropUnique('uq_work_schedule_staff_slot');
            $table->unique(
                ['employment_type', 'work_date', 'start_time', 'end_time'],
                'uq_work_schedule_type_slot'
            );
        });
    }

    // Rollback: trả lại ràng buộc unique cũ theo staff.
    public function down(): void
    {
        Schema::table('work_schedule_registrations', function (Blueprint $table) {
            $table->dropUnique('uq_work_schedule_type_slot');
            $table->unique(['staff_id', 'work_date', 'start_time', 'end_time'], 'uq_work_schedule_staff_slot');
        });
    }
};
