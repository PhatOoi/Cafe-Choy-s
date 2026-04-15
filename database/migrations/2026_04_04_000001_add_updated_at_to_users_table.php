<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Bổ sung updated_at để theo dõi lần cập nhật cuối của bản ghi user.
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('updated_at')->useCurrent()->nullable();
        });
    }

    // Xóa cột updated_at khi rollback migration này.
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
};
