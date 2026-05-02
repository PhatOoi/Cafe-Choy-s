<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_profits', function (Blueprint $table) {
            $table->id();
            $table->date('month_start')->unique();
            $table->decimal('ingredient_cost', 14, 2)->default(0);
            $table->decimal('electricity_cost', 14, 2)->default(0);
            $table->decimal('water_cost', 14, 2)->default(0);
            $table->decimal('service_cost', 14, 2)->default(0);
            $table->decimal('depreciation_cost', 14, 2)->default(0);
            $table->decimal('rent_cost', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_profits');
    }
};
