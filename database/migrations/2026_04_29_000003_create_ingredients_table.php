<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('unit', 30)->default('số lượng');
            $table->decimal('stock_quantity', 12, 2)->default(0);
            $table->decimal('minimum_quantity', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
