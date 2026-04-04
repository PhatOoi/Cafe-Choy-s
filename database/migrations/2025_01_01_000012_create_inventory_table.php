<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('unit', 30)->comment('kg / lít / hộp / túi / gói / cái');
            $table->decimal('min_stock', 10, 2)->default(0)
                  ->comment('Ngưỡng cảnh báo hết hàng');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventory');
            $table->foreignId('staff_id')->constrained('users');
            $table->string('type', 20)->comment('import / export / adjust');
            $table->decimal('quantity', 10, 2)->comment('Dương = nhập, âm = xuất');
            $table->string('note', 300)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('inventory_id', 'idx_invlog_inventory');
            $table->index('staff_id', 'idx_invlog_staff');
            $table->index('created_at', 'idx_invlog_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
        Schema::dropIfExists('inventory');
    }
};
