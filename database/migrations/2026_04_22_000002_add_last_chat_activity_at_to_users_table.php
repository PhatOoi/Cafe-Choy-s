<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration thêm cột last_chat_activity_at vào bảng users.
// Dùng để theo dõi lần cuối khách hàng gửi tin, phục vụ auto-delete chat sau 30 phút không hoạt động.
return new class extends Migration
{
    // Thêm cột timestamp nullable sau cột is_active.
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_chat_activity_at')->nullable()->after('is_active');
        });
    }

    // Rollback: xóa cột này khi revert.
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_chat_activity_at');
        });
    }
};
