<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tạo bảng extra và bảng pivot nối extra với sản phẩm.
    public function up(): void
    {
        // Bảng chính lưu topping/extra cùng mức giá cộng thêm.
        Schema::create('extras', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('price', 10, 2)->default(0);
        });

        // Pivot product_extras xác định sản phẩm nào được phép dùng extra nào.
        Schema::create('product_extras', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('extra_id')->constrained('extras')->cascadeOnDelete();
            $table->primary(['product_id', 'extra_id']);
        });
    }

    // Rollback theo thứ tự pivot trước, bảng chính sau để tránh vướng FK.
    public function down(): void
    {
        Schema::dropIfExists('product_extras');
        Schema::dropIfExists('extras');
    }
};
