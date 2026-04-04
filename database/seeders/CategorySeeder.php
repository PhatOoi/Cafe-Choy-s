<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            ['name' => 'Cà Phê', 'slug' => 'ca-phe'],
            ['name' => 'Trà Sữa', 'slug' => 'tra-sua'],
            ['name' => 'Đá Xay', 'slug' => 'da-xay'],
            ['name' => 'Nước Ép', 'slug' => 'nuoc-ep'],
            ['name' => 'Bánh', 'slug' => 'banh'],
        ]);
    }
}
