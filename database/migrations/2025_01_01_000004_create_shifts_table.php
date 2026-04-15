<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tạo bảng ca làm để lưu thời gian trực của nhân viên.
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->comment('FK → users (role=staff)');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->string('note', 300)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('staff_id', 'idx_shift_staff');
        });
    }

    // Xóa bảng shifts khi rollback.
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
