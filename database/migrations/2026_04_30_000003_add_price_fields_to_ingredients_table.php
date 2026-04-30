<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->decimal('unit_price', 12, 2)->default(0)->comment('Đơn giá nguyên liệu');
            $table->decimal('total_amount', 12, 2)->default(0)->comment('Tổng tiền = unit_price * stock_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'total_amount']);
        });
    }
};
