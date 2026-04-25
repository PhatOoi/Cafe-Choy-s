<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Migration thêm loại nhân viên để tách lịch full-time và part-time.
return new class extends Migration
{
    // Thêm cột employment_type vào users và backfill staff mẫu đang có.
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('employment_type', ['full_time', 'part_time'])
                ->nullable()
                ->after('role_id')
                ->comment('Chỉ áp dụng cho staff: full_time | part_time');
        });

        // Gán mặc định dữ liệu staff hiện có để bảng đăng ký giờ làm hiển thị được ngay.
        DB::table('users')
            ->where('role_id', 2)
            ->update(['employment_type' => 'part_time']);

        DB::table('users')
            ->where('email', 'nhan@coffeechoys.vn')
            ->update(['employment_type' => 'full_time']);
    }

    // Rollback: bỏ cột employment_type khỏi users.
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('employment_type');
        });
    }
};