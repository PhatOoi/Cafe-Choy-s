<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tạo bảng vai trò nền tảng để phân quyền admin, staff và customer.
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('admin / staff / customer');
            $table->string('description', 200)->nullable();
        });
    }

    // Xóa bảng vai trò khi rollback migration này.
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
