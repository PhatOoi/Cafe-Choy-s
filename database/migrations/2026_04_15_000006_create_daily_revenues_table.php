<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tạo bảng snapshot doanh thu ngày để staff xem báo cáo nhanh mà không phải query lại toàn bộ đơn.
    public function up(): void
    {
        Schema::create('daily_revenues', function (Blueprint $table) {
            $table->id();
            $table->date('revenue_date')->unique();
            $table->unsignedInteger('total_orders')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('staff_created_revenue', 12, 2)->default(0);
            $table->decimal('customer_revenue', 12, 2)->default(0);
            $table->decimal('cash_revenue', 12, 2)->default(0);
            $table->decimal('transfer_revenue', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    // Xóa bảng daily_revenues khi rollback migration.
    public function down(): void
    {
        Schema::dropIfExists('daily_revenues');
    }
};