<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Categories
        DB::table('categories')->insert([
            ['name' => 'Cà Phê', 'slug' => 'ca-phe', 'sort_order' => 1],
            ['name' => 'Trà Sữa', 'slug' => 'tra-sua', 'sort_order' => 2],
            ['name' => 'Đá Xay', 'slug' => 'da-xay', 'sort_order' => 3],
            ['name' => 'Nước Ép & Sinh Tố', 'slug' => 'nuoc-ep', 'sort_order' => 4],
            ['name' => 'Bánh & Snack', 'slug' => 'banh-snack', 'sort_order' => 5],
        ]);

        // Products
        DB::table('products')->insert([
            // ===== CÀ PHÊ (1) =====
            ['category_id' => 1, 'name' => 'Cà Phê Đen', 'description' => 'Cà phê nguyên chất', 'price' => 25000, 'stock' => 100, 'status' => 'available', 'image_url' => 'cafe_den.jpg', 'created_at' => now()],
            ['category_id' => 1, 'name' => 'Cà Phê Sữa', 'description' => 'Cà phê sữa truyền thống', 'price' => 30000, 'stock' => 100, 'status' => 'available', 'image_url' => 'cafe_sua.jpg', 'created_at' => now()],
            ['category_id' => 1, 'name' => 'Bạc Xỉu', 'description' => 'Sữa nhiều cà phê ít', 'price' => 32000, 'stock' => 80, 'status' => 'available', 'image_url' => 'bacxiu.jpg', 'created_at' => now()],
            ['category_id' => 1, 'name' => 'Cappuccino', 'description' => 'Cà phê Ý', 'price' => 45000, 'stock' => 80, 'status' => 'available', 'image_url' => 'cappuccino.jpg', 'created_at' => now()],
            ['category_id' => 1, 'name' => 'Latte', 'description' => 'Cà phê sữa mịn', 'price' => 45000, 'stock' => 80, 'status' => 'available', 'image_url' => 'latte.jpg', 'created_at' => now()],
            ['category_id' => 1, 'name' => 'Cà Phê Muối', 'description' => 'Cà phê muối truyền thống', 'price' => 40000, 'stock' => 70, 'status' => 'available', 'image_url' => 'cafemuoi.jpg', 'created_at' => now()],

            // ===== TRÀ SỮA (2) =====
            ['category_id' => 2, 'name' => 'Trà Sữa Trân Châu', 'description' => 'Trà sữa truyền thống', 'price' => 45000, 'stock' => 60, 'status' => 'available', 'image_url' => 'tstranchau.jpg', 'created_at' => now()],
            ['category_id' => 2, 'name' => 'Trà Sữa Matcha', 'description' => 'Matcha Nhật', 'price' => 50000, 'stock' => 60, 'status' => 'available', 'image_url' => 'tsmatcha.jpg', 'created_at' => now()],
            ['category_id' => 2, 'name' => 'Trà Sữa Caramel', 'description' => 'Caramel thơm', 'price' => 52000, 'stock' => 60, 'status' => 'available', 'image_url' => 'tscaramel.jpg', 'created_at' => now()],

            // ===== ĐÁ XAY (3) =====
            ['category_id' => 3, 'name' => 'Frappuccino Caramel', 'description' => 'Caramel đá xay', 'price' => 55000, 'stock' => 50, 'status' => 'available', 'image_url' => 'frappe_caramel.jpg', 'created_at' => now()],
            ['category_id' => 3, 'name' => 'Đá Xay Matcha', 'description' => 'Matcha đá xay', 'price' => 52000, 'stock' => 50, 'status' => 'available', 'image_url' => 'daxay_matcha.jpg', 'created_at' => now()],
            ['category_id' => 3, 'name' => 'Đá Xay Oreo', 'description' => 'Bánh Oreo', 'price' => 55000, 'stock' => 50, 'status' => 'available', 'image_url' => 'daxay_oreo.jpg', 'created_at' => now()],

            // ===== NƯỚC ÉP (4) =====
            ['category_id' => 4, 'name' => 'Nước Ép Cam', 'description' => 'Cam tươi', 'price' => 35000, 'stock' => 40, 'status' => 'available', 'image_url' => 'epcam.jpg', 'created_at' => now()],
            ['category_id' => 4, 'name' => 'Nước Ép Táo', 'description' => 'Táo đỏ', 'price' => 35000, 'stock' => 40, 'status' => 'available', 'image_url' => 'eptao.jpg', 'created_at' => now()],
            ['category_id' => 4, 'name' => 'Nước Ép Dưa Hấu', 'description' => 'Mát lạnh', 'price' => 30000, 'stock' => 40, 'status' => 'available', 'image_url' => 'epduahau.jpg', 'created_at' => now()],
            ['category_id' => 4, 'name' => 'Sinh Tố Bơ', 'description' => 'Bơ béo', 'price' => 45000, 'stock' => 30, 'status' => 'available', 'image_url' => 'sinhto_bo.jpg', 'created_at' => now()],
            ['category_id' => 4, 'name' => 'Sinh Tố Xoài', 'description' => 'Xoài chín', 'price' => 40000, 'stock' => 30, 'status' => 'available', 'image_url' => 'sinhto_xoai.jpg', 'created_at' => now()],
            ['category_id' => 4, 'name' => 'Sinh Tố Dâu', 'description' => 'Dâu tươi', 'price' => 40000, 'stock' => 30, 'status' => 'available', 'image_url' => 'sinhto_dau.jpg', 'created_at' => now()],

            // ===== BÁNH (5) =====
            ['category_id' => 5, 'name' => 'Bánh Sừng Bò', 'description' => 'Croissant', 'price' => 35000, 'stock' => 20, 'status' => 'available', 'image_url' => 'croissant.jpg', 'created_at' => now()],
            ['category_id' => 5, 'name' => 'Cookie Socola', 'description' => 'Cookie mềm', 'price' => 25000, 'stock' => 30, 'status' => 'available', 'image_url' => 'cookie.jpg', 'created_at' => now()],
            ['category_id' => 5, 'name' => 'Bánh Mì Bơ Tỏi', 'description' => 'Giòn thơm', 'price' => 20000, 'stock' => 30, 'status' => 'available', 'image_url' => 'banhmi.jpg', 'created_at' => now()],
            ['category_id' => 5, 'name' => 'Tiramisu', 'description' => 'Bánh Ý', 'price' => 45000, 'stock' => 20, 'status' => 'available', 'image_url' => 'tiramisu.jpg', 'created_at' => now()],
            ['category_id' => 5, 'name' => 'Cheesecake', 'description' => 'Bánh phô mai', 'price' => 45000, 'stock' => 20, 'status' => 'available', 'image_url' => 'cheesecake.jpg', 'created_at' => now()],
            ['category_id' => 5, 'name' => 'Donut', 'description' => 'Bánh vòng', 'price' => 20000, 'stock' => 30, 'status' => 'available', 'image_url' => 'donut.jpg', 'created_at' => now()],
        ]);

        // Extras
        DB::table('extras')->insert([
            ['name' => 'Trân Châu Đen', 'price' => 5000, 'type' => 'topping'],
            ['name' => 'Trân Châu Trắng', 'price' => 5000, 'type' => 'topping'],
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
            ['name' => 'Thạch', 'price' => 10000, 'type' => 'topping'],
            ['name' => 'Trân châu Hoàng Kim', 'price' => 5000, 'type' => 'topping'],
        ]);

        // Product extras (product_id => [extra_ids])
        $products = DB::table('products')
            ->where('category_id', '!=', 5) // bỏ bánh & snack
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

        DB::table('product_extras')->insert($rows);
    }
}
