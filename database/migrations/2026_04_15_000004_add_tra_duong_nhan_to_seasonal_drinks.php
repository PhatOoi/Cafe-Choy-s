<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categoryId = DB::table('categories')
            ->where('slug', 'tra-va-thuc-uong-theo-mua')
            ->value('id');

        if (!$categoryId) {
            return;
        }

        $existingId = DB::table('products')
            ->where('category_id', $categoryId)
            ->where('name', 'Trà Dưỡng Nhan')
            ->value('id');

        $payload = [
            'category_id' => $categoryId,
            'name' => 'Trà Dưỡng Nhan',
            'description' => 'Trà dưỡng nhan thanh nhẹ đúng theo hình ảnh đang dùng',
            'price' => 52000,
            'stock' => 30,
            'status' => 'available',
            'image_url' => 'tradn.jpg',
        ];

        if ($existingId) {
            DB::table('products')
                ->where('id', $existingId)
                ->update($payload);

            return;
        }

        DB::table('products')->insert($payload + [
            'created_at' => now(),
        ]);
    }

    public function down(): void
    {
        $categoryId = DB::table('categories')
            ->where('slug', 'tra-va-thuc-uong-theo-mua')
            ->value('id');

        if (!$categoryId) {
            return;
        }

        DB::table('products')
            ->where('category_id', $categoryId)
            ->where('name', 'Trà Dưỡng Nhan')
            ->delete();
    }
};
