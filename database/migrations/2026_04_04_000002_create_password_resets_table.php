<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tạo bảng lưu mã/token đặt lại mật khẩu theo email người dùng.
    public function up(): void
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    // Xóa bảng password_resets khi rollback.
    public function down(): void
    {
        Schema::dropIfExists('password_resets');
    }
};
