<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SizeSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('sizes')->count() === 0) {
            DB::table('sizes')->insert([
                ['name' => 'S', 'extra_price' => 0],
                ['name' => 'M', 'extra_price' => 10000],
                ['name' => 'L', 'extra_price' => 15000],
            ]);
        }
    }
}
