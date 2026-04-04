<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_item_extras', function (Blueprint $table) {
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('extra_id')->constrained();
            $table->string('extra_name');
            $table->decimal('extra_price', 10, 2);
            $table->primary(['order_item_id', 'extra_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_extras');
    }
};
