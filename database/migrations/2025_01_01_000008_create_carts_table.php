<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity')->default(1);
            $table->string('note', 200)->nullable()->comment('Ghi chú riêng cho item này');

            $table->index('cart_id', 'idx_ci_cart');
            $table->index('product_id', 'idx_ci_product');
        });

        Schema::create('cart_item_extras', function (Blueprint $table) {
            $table->foreignId('cart_item_id')->constrained('cart_items')->cascadeOnDelete();
            $table->foreignId('extra_id')->constrained('extras');
            $table->primary(['cart_item_id', 'extra_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_item_extras');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
