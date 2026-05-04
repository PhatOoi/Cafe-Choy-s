<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_profits', function (Blueprint $table) {
            $table->decimal('salary_cost', 14, 2)->default(0)->after('rent_cost');
            $table->decimal('monthly_revenue', 14, 2)->default(0)->after('salary_cost');
            $table->decimal('total_expense', 14, 2)->default(0)->after('monthly_revenue');
            $table->decimal('net_profit', 14, 2)->default(0)->after('total_expense');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_profits', function (Blueprint $table) {
            $table->dropColumn(['salary_cost', 'monthly_revenue', 'total_expense', 'net_profit']);
        });
    }
};
