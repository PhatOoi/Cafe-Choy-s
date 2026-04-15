<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Thêm hoặc cập nhật món Trà Trái Cây Nhiệt Đới trong nhóm seasonal drinks.
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

        // Nếu sản phẩm đã tồn tại thì chỉ cập nhật payload mới nhất.
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

    // Xóa món này khỏi category seasonal nếu rollback.
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
