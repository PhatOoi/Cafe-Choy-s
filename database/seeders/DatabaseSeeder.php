<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Thứ tự chạy QUAN TRỌNG — phải theo đúng thứ tự FK:
     *   1. UserRoleSeeder    — user_roles (không phụ thuộc)
     *   2. UserSeeder        — users (→ user_roles)
     *   3. AddressSeeder     — addresses (→ users)
     *   4. ShiftSeeder       — shifts (→ users)
     *   5. ProductSeeder     — categories + products + extras + product_extras
     *   6. CartSeeder        — carts + cart_items + cart_item_extras (→ users, products)
     *   7. VoucherSeeder     — vouchers + user_vouchers (→ users)
     *   8. OrderSeeder       — orders + order_items + order_item_extras + payments (→ tất cả)
     *   9. InventorySeeder   — inventory + inventory_logs (→ users)
     */
    // Gọi toàn bộ seeder theo đúng thứ tự phụ thuộc khóa ngoại.
    public function run(): void
    {
        $this->call([
            UserRoleSeeder::class,
            UserSeeder::class,
            AddressSeeder::class,
            ShiftSeeder::class,
            ProductSeeder::class,
            SizeSeeder::class,
            CartSeeder::class,
            VoucherSeeder::class,
            OrderSeeder::class,
            InventorySeeder::class,
            IngredientSeeder::class,
            PayrollAprilSampleSeeder::class,
        ]);
    }
}
