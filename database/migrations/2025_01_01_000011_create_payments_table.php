<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('method', 30)->comment('cod / bank_transfer / momo / vnpay / zalopay');
            $table->string('status', 20)->default('pending')
                  ->comment('pending / paid / failed / refunded');
            $table->decimal('amount', 10, 2)->comment('Số tiền thực tế thanh toán');
            $table->dateTime('paid_at')->nullable();
            $table->string('ref_code', 100)->nullable()
                  ->comment('Mã giao dịch từ cổng thanh toán');

            $table->index('order_id', 'idx_payment_order');
            $table->index('status', 'idx_payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
