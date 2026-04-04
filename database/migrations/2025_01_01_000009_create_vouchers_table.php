<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->decimal('discount', 10, 2)->comment('Số tiền hoặc % tuỳ type');
            $table->string('type', 10)->default('amount')->comment('amount / percent');
            $table->decimal('min_order_value', 10, 2)->default(0)
                  ->comment('Đơn tối thiểu mới áp dụng');
            $table->decimal('max_discount', 10, 2)->nullable()
                  ->comment('Giới hạn giảm tối đa (cho type=percent)');
            $table->integer('usage_limit')->nullable()
                  ->comment('Giới hạn tổng số lần dùng');
            $table->integer('used_count')->default(0);
            $table->dateTime('expired_at');
            $table->boolean('is_active')->default(true);
        });

        // user_vouchers: tạm chưa có order_id FK — sẽ thêm sau khi có bảng orders
        Schema::create('user_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->dateTime('used_at')->nullable()->comment('NULL = chưa dùng');
            $table->unsignedBigInteger('order_id')->nullable()->comment('Dùng cho đơn nào');
            $table->unique(['user_id', 'voucher_id'], 'uq_user_voucher');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_vouchers');
        Schema::dropIfExists('vouchers');
    }
};
