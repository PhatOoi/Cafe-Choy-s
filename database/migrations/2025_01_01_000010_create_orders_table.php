<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')
                  ->comment('Khách đặt hàng');
            $table->foreignId('address_id')->nullable()->nullOnDelete()
                  ->constrained('addresses')
                  ->comment('NULL = mua tại quán');
            $table->foreignId('assigned_staff_id')->nullable()->nullOnDelete()
                  ->constrained('users')
                  ->comment('Nhân viên phụ trách giao / tiếp nhận');
            $table->foreignId('voucher_id')->nullable()->nullOnDelete()
                  ->constrained('vouchers')
                  ->comment('Voucher áp dụng cho đơn');
            $table->string('order_type', 20)->default('delivery')
                  ->comment('delivery / in_store');
            $table->string('status', 30)->default('pending')
                  ->comment('pending/confirmed/processing/ready/delivering/delivered/failed/cancelled');
            $table->decimal('total_price', 10, 2)->comment('Tổng trước giảm');
            $table->decimal('discount_amount', 10, 2)->default(0)
                  ->comment('Số tiền được giảm');
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2)->comment('Tổng sau giảm + phí ship');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id', 'idx_order_user');
            $table->index('assigned_staff_id', 'idx_order_staff');
            $table->index('status', 'idx_order_status');
            $table->index('created_at', 'idx_order_date');
        });

        // Thêm FK order_id vào user_vouchers sau khi bảng orders đã tồn tại
        Schema::table('user_vouchers', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });

        // Đã chuyển tạo bảng order_items sang file migration riêng

        // Đã chuyển tạo bảng order_item_extras sang file migration riêng
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_extras');
        Schema::dropIfExists('order_items');

        Schema::table('user_vouchers', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::dropIfExists('orders');
    }
};
