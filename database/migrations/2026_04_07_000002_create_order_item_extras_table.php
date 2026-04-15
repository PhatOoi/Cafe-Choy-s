<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tạo bảng pivot extra/topping cho từng order item.
    public function up(): void
    {
        Schema::create('order_item_extras', function (Blueprint $table) {
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('extra_id')->constrained('extras');
            $table->string('extra_name', 100)->comment('Snapshot tên topping lúc đặt');
            $table->decimal('extra_price', 10, 2)->comment('Snapshot giá topping lúc đặt');
            $table->primary(['order_item_id', 'extra_id']);
        });
    }

    // Xóa bảng order_item_extras khi rollback.
    public function down(): void
    {
        Schema::dropIfExists('order_item_extras');
    }
};
