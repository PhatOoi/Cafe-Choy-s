<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ingredient_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->string('ingredient_name', 120);      // snapshot tên lúc xuất
            $table->decimal('quantity', 10, 2);           // sl lấy ra
            $table->string('unit', 30);                   // đơn vị
            $table->decimal('stock_before', 10, 2);       // tồn trước khi xuất
            $table->decimal('stock_after', 10, 2);        // tồn sau khi xuất
            $table->string('note', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_withdrawals');
    }
};
