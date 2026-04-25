<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration tạo bảng lưu trạng thái khóa bảng đăng ký giờ làm theo tuần.
return new class extends Migration
{
    // Tạo bảng work_schedule_board_locks để chặn đăng ký mới sau khi admin đóng bảng tuần.
    public function up(): void
    {
        Schema::create('work_schedule_board_locks', function (Blueprint $table) {
            $table->id();
            $table->date('week_start')->unique()->comment('Ngày bắt đầu tuần (thứ 2)');
            $table->date('week_end')->comment('Ngày kết thúc tuần (chủ nhật)');
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable()->comment('Thời điểm admin đóng bảng tuần');
            $table->timestamps();

            $table->index(['week_start', 'week_end'], 'idx_work_schedule_board_week');
        });
    }

    // Rollback: xóa bảng khóa đăng ký giờ làm theo tuần.
    public function down(): void
    {
        Schema::dropIfExists('work_schedule_board_locks');
    }
};