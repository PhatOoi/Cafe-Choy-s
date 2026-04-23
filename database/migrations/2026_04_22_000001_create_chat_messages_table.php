<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration tạo bảng chat_messages — lưu toàn bộ tin nhắn giữa khách hàng và nhân viên hỗ trợ.
return new class extends Migration
{
    // Tạo bảng khi chạy migrate.
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // khách hàng chủ sở hữu cuộc chat
            $table->text('message'); // nội dung tin nhắn
            $table->enum('sender', ['customer', 'staff']); // ai gửi: khách hay nhân viên
            $table->foreignId('replied_by')->nullable()->constrained('users')->onDelete('set null'); // staff đã reply (nullable)
            $table->boolean('is_read')->default(false); // đầu đọc phục vụ badge unread
            $table->timestamps();
        });
    }

    // Rollback: xóa bảng khi revert migration.
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
