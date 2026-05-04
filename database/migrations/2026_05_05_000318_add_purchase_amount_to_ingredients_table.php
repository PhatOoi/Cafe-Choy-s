<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            // Giá trị nhập kho cố định: stock_quantity * unit_price lúc thêm, kông thay đổi khi xuất
            $table->decimal('purchase_amount', 14, 2)->default(0)->after('total_amount');
        });

        // Back-fill cho các dòng đã tồn tại: lấy total_amount hiện tại làm giá trị nhập kho
        DB::statement('UPDATE ingredients SET purchase_amount = total_amount');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn('purchase_amount');
        });
    }
};
