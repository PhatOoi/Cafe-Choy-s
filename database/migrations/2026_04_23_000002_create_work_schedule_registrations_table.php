<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration tạo bảng đăng ký giờ làm của nhân viên.
return new class extends Migration
{
    // Tạo bảng work_schedule_registrations để lưu các slot đăng ký ca theo ngày.
    public function up(): void
    {
        Schema::create('work_schedule_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete()->comment('Nhân viên đăng ký giờ làm');
            $table->enum('employment_type', ['full_time', 'part_time'])->comment('Snapshot loại nhân viên tại thời điểm đăng ký');
            $table->date('work_date')->comment('Ngày làm việc đã đăng ký');
            $table->time('start_time')->comment('Giờ bắt đầu ca');
            $table->time('end_time')->comment('Giờ kết thúc ca');
            $table->string('shift_label', 120)->nullable()->comment('Tên ca gợi nhớ, ví dụ: Ca sáng');
            $table->string('note', 300)->nullable()->comment('Ghi chú thêm của nhân viên');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['employment_type', 'work_date'], 'idx_work_schedule_type_date');
            $table->index('staff_id', 'idx_work_schedule_staff');
            $table->unique(['staff_id', 'work_date', 'start_time', 'end_time'], 'uq_work_schedule_staff_slot');
        });
    }

    // Rollback: xóa bảng đăng ký giờ làm.
    public function down(): void
    {
        Schema::dropIfExists('work_schedule_registrations');
    }
};