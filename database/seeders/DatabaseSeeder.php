<?php

namespace Database\Seeders;

public function run(): void
{
    $this->call([
        UserRoleSeeder::class,
        UserSeeder::class,
        CategorySeeder::class,
        ProductSeeder::class,
        ExtraSeeder::class,
        CartSeeder::class,
        OrderSeeder::class,
        InventorySeeder::class,
        VoucherSeeder::class,
    ]);
}
