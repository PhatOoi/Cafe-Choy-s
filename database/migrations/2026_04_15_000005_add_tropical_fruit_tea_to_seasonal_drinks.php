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
            ->where('name', 'Trà Trái Cây Nhiệt Đới')
            ->value('id');

        $payload = [
            'category_id' => $categoryId,
            'name' => 'Trà Trái Cây Nhiệt Đới',
            'description' => 'Bùng nổ vị trái cây tươi mát, chua ngọt sảng khoái và thơm dịu như một kỳ nghỉ mùa hè trong từng ngụm',
            'price' => 53000,
            'stock' => 32,
            'status' => 'available',
            'image_url' => 'tratraicaynhietdoi.jpg',
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
            ->where('name', 'Trà Trái Cây Nhiệt Đới')
            ->delete();
    }
};
