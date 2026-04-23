<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration tạo bảng payments — lưu thông tin thanh toán gắn liền với từng đơn hàng.
return new class extends Migration
{
    // Tạo bảng khi chạy migrate.
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete(); // đơn hàng của payment này
            $table->string('method', 30)->comment('cod / bank_transfer / momo / vnpay / zalopay');
            $table->string('status', 20)->default('pending')
                  ->comment('pending / paid / failed / refunded');
            $table->decimal('amount', 10, 2)->comment('Số tiền thực tế thanh toán');
            $table->dateTime('paid_at')->nullable(); // thời điểm xác nhận thành công
            $table->string('ref_code', 100)->nullable()
                  ->comment('Mã giao dịch từ cổng thanh toán');

            $table->index('order_id', 'idx_payment_order');
            $table->index('status', 'idx_payment_status');
        });
    }

    // Rollback: xóa bảng khi revert migration.
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
