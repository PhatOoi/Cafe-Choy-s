<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration thêm trạng thái duyệt/đóng cho từng bản đăng ký giờ làm.
return new class extends Migration
{
    // Bổ sung trạng thái và dấu vết người duyệt/đóng để admin quản lý bảng đăng ký giờ làm.
    public function up(): void
    {
        Schema::table('work_schedule_registrations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'closed'])
                ->default('pending')
                ->after('note')
                ->comment('pending: chờ duyệt, approved: đã duyệt, closed: đã khóa tính lương');
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->foreignId('closed_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable()->after('closed_by');
        });
    }

    // Rollback: bỏ các cột trạng thái của đăng ký giờ làm.
    public function down(): void
    {
        Schema::table('work_schedule_registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('closed_by');
            $table->dropColumn('closed_at');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn('approved_at');
            $table->dropColumn('status');
        });
    }
};