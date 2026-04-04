<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->default(3)->constrained('user_roles');
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('password', 255)->comment('bcrypt — KHÔNG lưu plaintext');
            $table->string('phone', 20)->nullable();
            $table->string('avatar_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index('role_id', 'idx_user_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
