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
            ['name' => 'Cà Phê',           'slug' => 'ca-phe',      'sort_order' => 1],
            ['name' => 'Trà Sữa',          'slug' => 'tra-sua',     'sort_order' => 2],
            ['name' => 'Đá Xay',           'slug' => 'da-xay',      'sort_order' => 3],
            ['name' => 'Nước Ép & Sinh Tố','slug' => 'nuoc-ep',     'sort_order' => 4],
            ['name' => 'Bánh & Snack',     'slug' => 'banh-snack',  'sort_order' => 5],
        ]);

        // Products
        DB::table('products')->insert([
            ['category_id' => 1, 'name' => 'Cà Phê Đen',         'description' => 'Cà phê nguyên chất pha phin truyền thống',       'price' => 25000, 'stock' => 100, 'status' => 'available', 'image_url' => 'cafe_den.jpg', 'created_at' => now()],
            ['category_id' => 1, 'name' => 'Cà Phê Sữa',         'description' => 'Cà phê phin kết hợp sữa đặc thơm ngon',         'price' => 30000, 'stock' => 100, 'status' => 'available', 'image_url' => 'cafe_sua.jpg', 'created_at' => now()],
            ['category_id' => 1, 'name' => 'Cappuccino',          'description' => 'Espresso kết hợp sữa tươi đánh bọt mịn',        'price' => 45000, 'stock' => 80,  'status' => 'available', 'image_url' => 'cappuccino.jpg', 'created_at' => now()],
            ['category_id' => 1, 'name' => 'Latte',               'description' => 'Espresso với lớp sữa mịn và ít bọt',            'price' => 45000, 'stock' => 80,  'status' => 'available', 'image_url' => 'latte.jpg', 'created_at' => now()],
            ['category_id' => 2, 'name' => 'Trà Sữa Trân Châu',  'description' => 'Trà đen với sữa tươi và trân châu đen',         'price' => 45000, 'stock' => 60,  'status' => 'available', 'image_url' => 'tstranchau.jpg', 'created_at' => now()],
            ['category_id' => 2, 'name' => 'Trà Sữa Matcha',     'description' => 'Matcha Nhật Bản hoà tan với sữa tươi béo ngậy', 'price' => 50000, 'stock' => 60,  'status' => 'available', 'image_url' => 'tsmatcha.jpg', 'created_at' => now()],
            ['category_id' => 3, 'name' => 'Frappuccino Caramel', 'description' => 'Đá xay cà phê caramel phủ whipping cream',      'price' => 55000, 'stock' => 50,  'status' => 'available', 'image_url' => 'FrappuccinoCaramel.jpg', 'created_at' => now()],
            ['category_id' => 3, 'name' => 'Đá Xay Chocolate',   'description' => 'Đá xay chocolate đậm đà mát lạnh',              'price' => 50000, 'stock' => 50,  'status' => 'available', 'image_url' => 'da-xay-chocolate.jpg', 'created_at' => now()],
            ['category_id' => 4, 'name' => 'Nước Ép Cam',        'description' => 'Cam tươi vắt nguyên chất 100%',                 'price' => 35000, 'stock' => 40,  'status' => 'available', 'image_url' => 'epcam.jpg', 'created_at' => now()],
            ['category_id' => 4, 'name' => 'Sinh Tố Bơ',         'description' => 'Bơ chín mịn xay với sữa tươi và đá',           'price' => 45000, 'stock' => 30,  'status' => 'available', 'image_url' => 'drink-7.jpg', 'created_at' => now()],
            ['category_id' => 5, 'name' => 'Bánh Sừng Bò',       'description' => 'Croissant bơ Pháp nướng giòn mỗi sáng',        'price' => 35000, 'stock' => 20,  'status' => 'available', 'image_url' => 'banhsung.jpg', 'created_at' => now()],
            ['category_id' => 5, 'name' => 'Cookie Socola',      'description' => 'Bánh quy mềm chip socola nguyên tảng',          'price' => 25000, 'stock' => 30,  'status' => 'available', 'image_url' => 'dessert-1.jpg', 'created_at' => now()],
        ]);

        // Extras
        DB::table('extras')->insert([
            ['name' => 'Trân Châu Đen',            'price' => 5000, 'type' => 'topping'],
            ['name' => 'Trân Châu Trắng',          'price' => 5000, 'type' => 'topping'],
            ['name' => 'Thạch Cà Phê',             'price' => 5000, 'type' => 'topping'],
            ['name' => 'Thêm Espresso',            'price' => 10000, 'type' => 'topping'],
            ['name' => 'Ít Đường',                 'price' => 0, 'type' => 'sugar'],
            ['name' => 'Không Đường',              'price' => 0, 'type' => 'sugar'],
            ['name' => 'Nhiều Đá',                 'price' => 0, 'type' => 'ice'],
            ['name' => 'Ít Đá',                    'price' => 0, 'type' => 'ice'],
            ['name' => 'Whipping Cream',           'price' => 10000, 'type' => 'topping'],
            ['name' => 'Sữa Tươi Thay Sữa Đặc',    'price' => 5000, 'type' => 'topping'],
        ]);

        // Product extras (product_id => [extra_ids])
        $productExtras = [
            1 => [4, 5, 6, 7, 8],           // Cà Phê Đen
            2 => [4, 5, 6, 7, 8, 10],       // Cà Phê Sữa
            3 => [4, 9],                     // Cappuccino
            4 => [4, 9],                     // Latte
            5 => [1, 2, 3, 5, 6, 7, 8],     // Trà Sữa Trân Châu
            6 => [1, 2, 3, 5, 6, 7, 8],     // Trà Sữa Matcha
            7 => [4, 9],                     // Frappuccino Caramel
            8 => [9],                        // Đá Xay Chocolate
        ];

        $rows = [];
        foreach ($productExtras as $productId => $extraIds) {
            foreach ($extraIds as $extraId) {
                $rows[] = ['product_id' => $productId, 'extra_id' => $extraId];
            }
        }
        DB::table('product_extras')->insert($rows);
    }
}
