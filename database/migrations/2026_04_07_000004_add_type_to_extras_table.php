<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Thêm cột type để phân loại extra thành topping, đường, đá hoặc nhóm khác.
    public function up(): void
    {
        Schema::table('extras', function (Blueprint $table) {
            $table->string('type')->nullable()->after('price');
        });
    }

    // Xóa cột type nếu rollback migration này.
    public function down(): void
    {
        Schema::table('extras', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
