<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Tạo category đồ uống theo mùa và thêm các sản phẩm seasonal mặc định.
    public function up(): void
    {
        // Đảm bảo category seasonal tồn tại, nếu có rồi thì cập nhật lại thông tin.
        DB::table('categories')->updateOrInsert(
            ['slug' => 'tra-va-thuc-uong-theo-mua'],
            [
                'name' => 'Trà và Thức Uống Theo Mùa',
                'description' => 'Các món trà trái cây và thức uống theo mùa.',
                'sort_order' => 5,
            ]
        );

        // Đẩy nhóm bánh/snack xuống sau category seasonal mới.
        DB::table('categories')
            ->where('slug', 'banh-snack')
            ->update(['sort_order' => 6]);

        // Lấy id category vừa tạo để gắn sản phẩm seasonal vào đúng nhóm.
        $categoryId = DB::table('categories')
            ->where('slug', 'tra-va-thuc-uong-theo-mua')
            ->value('id');

        if (!$categoryId) {
            return;
        }

        $products = [
            [
                'category_id' => $categoryId,
                'name' => 'Trà Đào Cam Sả',
                'description' => 'Trà đào thanh mát với cam lát và sả',
                'price' => 42000,
                'stock' => 45,
                'status' => 'available',
                'image_url' => 'tra_dao_cam_sa.jpg',
                'created_at' => now(),
            ],
            [
                'category_id' => $categoryId,
                'name' => 'Trà Vải Hoa Hồng',
                'description' => 'Vị vải ngọt dịu hòa cùng hương hoa hồng',
                'price' => 45000,
                'stock' => 40,
                'status' => 'available',
                'image_url' => 'tra_vai_hoa_hong.jpg',
                'created_at' => now(),
            ],
            [
                'category_id' => $categoryId,
                'name' => 'Trà Oolong Nhãn',
                'description' => 'Trà oolong thơm nhẹ kết hợp nhãn tươi',
                'price' => 47000,
                'stock' => 38,
                'status' => 'available',
                'image_url' => 'tra_oolong_nhan.jpg',
                'created_at' => now(),
            ],
            [
                'category_id' => $categoryId,
                'name' => 'Matcha Dâu Mùa Hè',
                'description' => 'Matcha sữa mát lạnh cùng sốt dâu',
                'price' => 49000,
                'stock' => 35,
                'status' => 'available',
                'image_url' => 'matcha_dau_mua_he.jpg',
                'created_at' => now(),
            ],
        ];

        // Chỉ insert sản phẩm seasonal khi chưa tồn tại để migration có thể chạy lặp lại an toàn.
        foreach ($products as $product) {
            $exists = DB::table('products')
                ->where('category_id', $product['category_id'])
                ->where('name', $product['name'])
                ->exists();

            if (!$exists) {
                DB::table('products')->insert($product);
            }
        }
    }

    // Gỡ các sản phẩm seasonal đã thêm và trả lại sort_order cũ cho nhóm bánh/snack.
    public function down(): void
    {
        $categoryId = DB::table('categories')
            ->where('slug', 'tra-va-thuc-uong-theo-mua')
            ->value('id');

        if ($categoryId) {
            DB::table('products')
                ->where('category_id', $categoryId)
                ->whereIn('name', [
                    'Trà Đào Cam Sả',
                    'Trà Vải Hoa Hồng',
                    'Trà Oolong Nhãn',
                    'Matcha Dâu Mùa Hè',
                ])
                ->delete();

            DB::table('categories')
                ->where('id', $categoryId)
                ->delete();
        }

        DB::table('categories')
            ->where('slug', 'banh-snack')
            ->update(['sort_order' => 5]);
    }
};
