<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->string('brand', 120)->nullable()->after('name');
            $table->date('manufacture_date')->nullable()->after('minimum_quantity');
            $table->date('expiry_date')->nullable()->after('manufacture_date');
            $table->string('lot_number', 80)->nullable()->after('expiry_date');
            $table->index('lot_number');
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropIndex(['lot_number']);
            $table->dropColumn(['brand', 'manufacture_date', 'expiry_date', 'lot_number']);
        });
    }
};
