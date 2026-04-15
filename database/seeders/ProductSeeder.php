<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Categories
        $categories = [
            ['name' => 'Cà Phê', 'slug' => 'ca-phe', 'sort_order' => 1],
            ['name' => 'Trà Sữa', 'slug' => 'tra-sua', 'sort_order' => 2],
            ['name' => 'Đá Xay', 'slug' => 'da-xay', 'sort_order' => 3],
            ['name' => 'Nước Ép & Sinh Tố', 'slug' => 'nuoc-ep', 'sort_order' => 4],
            ['name' => 'Trà và Thức Uống Theo Mùa', 'slug' => 'tra-va-thuc-uong-theo-mua', 'sort_order' => 5],
            ['name' => 'Bánh & Snack', 'slug' => 'banh-snack', 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'sort_order' => $category['sort_order'],
                ]
            );
        }

        $categoryIds = DB::table('categories')->pluck('id', 'slug');

        // Products
        $products = [
            // ===== CÀ PHÊ (1) =====
            ['category_id' => $categoryIds['ca-phe'], 'name' => 'Cà Phê Đen', 'description' => 'Cà phê nguyên chất', 'price' => 25000, 'stock' => 100, 'status' => 'available', 'image_url' => 'cafe_den.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['ca-phe'], 'name' => 'Cà Phê Sữa', 'description' => 'Cà phê sữa truyền thống', 'price' => 30000, 'stock' => 100, 'status' => 'available', 'image_url' => 'cafe_sua.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['ca-phe'], 'name' => 'Bạc Xỉu', 'description' => 'Sữa nhiều cà phê ít', 'price' => 32000, 'stock' => 80, 'status' => 'available', 'image_url' => 'bacxiu.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['ca-phe'], 'name' => 'Cappuccino', 'description' => 'Cà phê Ý', 'price' => 45000, 'stock' => 80, 'status' => 'available', 'image_url' => 'cappuccino.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['ca-phe'], 'name' => 'Latte', 'description' => 'Cà phê sữa mịn', 'price' => 45000, 'stock' => 80, 'status' => 'available', 'image_url' => 'latte.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['ca-phe'], 'name' => 'Cà Phê Muối', 'description' => 'Cà phê muối truyền thống', 'price' => 40000, 'stock' => 70, 'status' => 'available', 'image_url' => 'cafemuoi.jpg', 'created_at' => now()],

            // ===== TRÀ SỮA (2) =====
            ['category_id' => $categoryIds['tra-sua'], 'name' => 'Trà Sữa Trân Châu', 'description' => 'Trà sữa truyền thống', 'price' => 45000, 'stock' => 60, 'status' => 'available', 'image_url' => 'tstranchau.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['tra-sua'], 'name' => 'Trà Sữa Matcha', 'description' => 'Matcha Nhật', 'price' => 50000, 'stock' => 60, 'status' => 'available', 'image_url' => 'tsmatcha.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['tra-sua'], 'name' => 'Trà Sữa Thái Đỏ', 'description' => 'Thái đỏ đặc trưng', 'price' => 52000, 'stock' => 60, 'status' => 'available', 'image_url' => 'tsthaido.jpg', 'created_at' => now()],

            // ===== ĐÁ XAY (3) =====
            ['category_id' => $categoryIds['da-xay'], 'name' => 'Frappuccino Caramel', 'description' => 'Caramel đá xay', 'price' => 55000, 'stock' => 50, 'status' => 'available', 'image_url' => 'frappe_caramel.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['da-xay'], 'name' => 'Đá Xay Matcha', 'description' => 'Matcha đá xay', 'price' => 52000, 'stock' => 50, 'status' => 'available', 'image_url' => 'daxay_matcha.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['da-xay'], 'name' => 'Đá Xay Oreo', 'description' => 'Bánh Oreo', 'price' => 55000, 'stock' => 50, 'status' => 'available', 'image_url' => 'daxay_oreo.jpg', 'created_at' => now()],

            // ===== NƯỚC ÉP (4) =====
            ['category_id' => $categoryIds['nuoc-ep'], 'name' => 'Nước Ép Cam', 'description' => 'Cam tươi', 'price' => 35000, 'stock' => 40, 'status' => 'available', 'image_url' => 'epcam.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['nuoc-ep'], 'name' => 'Nước Ép Táo', 'description' => 'Táo đỏ', 'price' => 35000, 'stock' => 40, 'status' => 'available', 'image_url' => 'eptao.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['nuoc-ep'], 'name' => 'Nước Ép Dưa Hấu', 'description' => 'Mát lạnh', 'price' => 30000, 'stock' => 40, 'status' => 'available', 'image_url' => 'epduahau.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['nuoc-ep'], 'name' => 'Sinh Tố Bơ', 'description' => 'Bơ béo', 'price' => 45000, 'stock' => 30, 'status' => 'available', 'image_url' => 'sinhto_bo.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['nuoc-ep'], 'name' => 'Sinh Tố Xoài', 'description' => 'Xoài chín', 'price' => 40000, 'stock' => 30, 'status' => 'available', 'image_url' => 'sinhto_xoai.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['nuoc-ep'], 'name' => 'Sinh Tố Dâu', 'description' => 'Dâu tươi', 'price' => 40000, 'stock' => 30, 'status' => 'available', 'image_url' => 'sinhto_dau.jpg', 'created_at' => now()],

            // ===== TRÀ VÀ THỨC UỐNG THEO MÙA (5) =====
            ['category_id' => $categoryIds['tra-va-thuc-uong-theo-mua'], 'name' => 'Peach Tea', 'description' => 'Trà đào thanh mát ', 'price' => 42000, 'stock' => 45, 'status' => 'available', 'image_url' => 'tra_dao_cam_sa.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['tra-va-thuc-uong-theo-mua'], 'name' => 'Trà Lài', 'description' => 'Trà lài thơm nhẹ ', 'price' => 45000, 'stock' => 40, 'status' => 'available', 'image_url' => 'tra_vai_hoa_hong.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['tra-va-thuc-uong-theo-mua'], 'name' => 'Trà Oolong Thiết Quan Âm', 'description' => 'Trà oolong Thiết Quan Âm tốt cho sức khỏe', 'price' => 47000, 'stock' => 38, 'status' => 'available', 'image_url' => 'tra_oolong_nhan.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['tra-va-thuc-uong-theo-mua'], 'name' => 'Trà Dâu', 'description' => 'Trà dâu thanh mát chua ngọt đánh thức vị giác', 'price' => 49000, 'stock' => 35, 'status' => 'available', 'image_url' => 'tradau.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['tra-va-thuc-uong-theo-mua'], 'name' => 'Trà Dưỡng Nhan', 'description' => 'Trà dưỡng nhan thanh nhẹ, ngọt dịu', 'price' => 52000, 'stock' => 30, 'status' => 'available', 'image_url' => 'tradn.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['tra-va-thuc-uong-theo-mua'], 'name' => 'Trà Trái Cây Nhiệt Đới', 'description' => 'Bùng nổ vị trái cây tươi mát, chua ngọt sảng khoái và thơm dịu như một kỳ nghỉ mùa hè trong từng ngụm', 'price' => 53000, 'stock' => 32, 'status' => 'available', 'image_url' => 'tratraicaynhietdoi.jpg', 'created_at' => now()],

            // ===== BÁNH (5) =====
            ['category_id' => $categoryIds['banh-snack'], 'name' => 'Bánh Sừng Bò', 'description' => 'Croissant', 'price' => 35000, 'stock' => 20, 'status' => 'available', 'image_url' => 'croissant.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['banh-snack'], 'name' => 'Cookie Chocolate', 'description' => 'Cookie mềm', 'price' => 7000, 'stock' => 30, 'status' => 'available', 'image_url' => 'cookie.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['banh-snack'], 'name' => 'Bánh Mì Bơ Tỏi', 'description' => 'Giòn thơm', 'price' => 20000, 'stock' => 30, 'status' => 'available', 'image_url' => 'banhmi.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['banh-snack'], 'name' => 'Tiramisu', 'description' => 'Bánh Ý', 'price' => 45000, 'stock' => 20, 'status' => 'available', 'image_url' => 'tiramisu.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['banh-snack'], 'name' => 'Cheesecake', 'description' => 'Bánh phô mai', 'price' => 45000, 'stock' => 20, 'status' => 'available', 'image_url' => 'cheesecake.jpg', 'created_at' => now()],
            ['category_id' => $categoryIds['banh-snack'], 'name' => 'Donut', 'description' => 'Bánh vòng', 'price' => 20000, 'stock' => 30, 'status' => 'available', 'image_url' => 'donut.jpg', 'created_at' => now()],
        ];
//tét

        foreach ($products as $product) {
            DB::table('products')->updateOrInsert(
                [
                    'category_id' => $product['category_id'],
                    'name' => $product['name'],
                ],
                [
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'status' => $product['status'],
                    'image_url' => $product['image_url'],
                    'created_at' => $product['created_at'],
                ]
            );
        }

        // Extras
        $extras = [
            ['name' => 'Trân Châu Đen', 'price' => 5000, 'type' => 'topping'],
            ['name' => 'Trân Châu Hoàng Kim', 'price' => 5000, 'type' => 'topping'],
            ['name' => 'Thạch Cà Phê', 'price' => 5000, 'type' => 'topping'],
            ['name' => 'Kem Trứng', 'price' => 12000, 'type' => 'topping'],
            ['name' => 'Nhiều Sữa', 'price' => 0, 'type' => 'sugar'],
            ['name' => 'Ít Sữa', 'price' => 0, 'type' => 'sugar'],
            ['name' => 'Không Sữa', 'price' => 0, 'type' => 'sugar'],
            ['name' => 'Nhiều Đường', 'price' => 0, 'type' => 'sugar'],
            ['name' => 'Ít Đường', 'price' => 0, 'type' => 'sugar'],
            ['name' => 'Không Đường', 'price' => 0, 'type' => 'sugar'],
            ['name' => 'Nhiều Đá', 'price' => 0, 'type' => 'ice'],
            ['name' => 'Ít Đá', 'price' => 0, 'type' => 'ice'],
            ['name' => 'Đá Riêng', 'price' => 0, 'type' => 'ice'],
            ['name' => 'Phô Mai Tươi', 'price' => 10000, 'type' => 'topping'],
            ['name' => 'Kem Macchiato', 'price' => 10000, 'type' => 'topping'],
        ];

        foreach ($extras as $extra) {
            DB::table('extras')->updateOrInsert(
                [
                    'name' => $extra['name'],
                    'type' => $extra['type'],
                ],
                ['price' => $extra['price']]
            );
        }

        // Product extras (product_id => [extra_ids])
        $products = DB::table('products')
            ->where('category_id', '!=', $categoryIds['banh-snack']) // bỏ bánh & snack
            ->pluck('id');

        $rows = [];

        foreach ($products as $productId) {
            foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9] as $extraId) {
                $rows[] = [
                    'product_id' => $productId,
                    'extra_id' => $extraId
                ];
            }
        }

        DB::table('product_extras')->insertOrIgnore($rows);
    }
}
