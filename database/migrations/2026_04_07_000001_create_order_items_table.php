<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->comment('Giá tại thời điểm đặt — snapshot');
            $table->string('note', 200)->nullable();

            $table->index('order_id', 'idx_oi_order');
            $table->index('product_id', 'idx_oi_product');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
