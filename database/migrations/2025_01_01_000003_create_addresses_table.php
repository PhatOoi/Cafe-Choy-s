<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('label', 50)->nullable()->comment('Nhà / Công ty / Khác');
            $table->string('address_line', 300);
            $table->string('district', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->decimal('lat', 10, 7)->nullable()->comment('Vĩ độ — Google Maps');
            $table->decimal('lng', 10, 7)->nullable()->comment('Kinh độ — Google Maps');
            $table->boolean('is_default')->default(false);

            $table->index('user_id', 'idx_addr_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
