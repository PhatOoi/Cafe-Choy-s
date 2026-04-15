<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Chuyển món seasonal cũ thành Trà Dâu để khớp với menu/ảnh thực tế mới.
    public function up(): void
    {
        DB::table('products')
            ->where(function ($query) {
                $query->where('image_url', 'matcha_dau_mua_he.jpg')
                    ->orWhere('name', 'Trà Sữa Thái Xanh');
            })
            ->update([
                'name' => 'Trà Dâu',
                'description' => 'Trà dâu thanh mát đúng theo hình ảnh đang dùng',
                'image_url' => 'tradau.jpg',
            ]);
    }

    // Khôi phục lại tên và ảnh cũ của món seasonal nếu rollback.
    public function down(): void
    {
        DB::table('products')
            ->where(function ($query) {
                $query->where('image_url', 'tradau.jpg')
                    ->orWhere('name', 'Trà Dâu');
            })
            ->where('category_id', function ($query) {
                $query->select('id')
                    ->from('categories')
                    ->where('slug', 'tra-va-thuc-uong-theo-mua')
                    ->limit(1);
            })
            ->update([
                'name' => 'Trà Sữa Thái Xanh',
                'description' => 'Trà sữa thái xanh đúng theo hình ảnh đang dùng',
                'image_url' => 'matcha_dau_mua_he.jpg',
            ]);
    }
};
