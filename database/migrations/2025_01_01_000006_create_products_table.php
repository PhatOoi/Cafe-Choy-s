<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0)->comment('Số lượng hiện có');
            $table->string('image_url', 500)->nullable();
            $table->string('status', 20)->default('available')
                  ->comment('available / unavailable / deleted');
            $table->timestamp('created_at')->useCurrent();

            $table->index('category_id', 'idx_product_category');
            $table->index('status', 'idx_product_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
