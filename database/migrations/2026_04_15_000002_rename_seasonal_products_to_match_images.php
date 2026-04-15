<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Đổi tên/mô tả sản phẩm seasonal để khớp với bộ ảnh đang dùng trên giao diện.
    public function up(): void
    {
        $renames = [
            'tra_dao_cam_sa.jpg' => [
                'name' => 'Peach Tea',
                'description' => 'Trà đào thanh mát đúng theo hình ảnh đang dùng',
            ],
            'tra_vai_hoa_hong.jpg' => [
                'name' => 'Trà Lài',
                'description' => 'Trà lài thơm nhẹ theo hình ảnh đang dùng',
            ],
            'tra_oolong_nhan.jpg' => [
                'name' => 'Trà Olong thiết quan âm',
                'description' => 'Trà Olong thiết quan âm đúng theo hình ảnh đang dùng',
            ],
            'matcha_dau_mua_he.jpg' => [
                'name' => 'Trà Sữa Thái Xanh',
                'description' => 'Trà sữa thái xanh đúng theo hình ảnh đang dùng',
            ],
        ];

        // Cập nhật theo image_url để tránh phụ thuộc vào tên cũ có thể đã bị thay đổi.
        foreach ($renames as $imageUrl => $productData) {
            DB::table('products')
                ->where('image_url', $imageUrl)
                ->update($productData);
        }
    }

    // Khôi phục lại tên/mô tả seasonal ban đầu nếu rollback.
    public function down(): void
    {
        $renames = [
            'tra_dao_cam_sa.jpg' => [
                'name' => 'Trà Đào Cam Sả',
                'description' => 'Trà đào thanh mát với cam lát và sả',
            ],
            'tra_vai_hoa_hong.jpg' => [
                'name' => 'Trà Vải Hoa Hồng',
                'description' => 'Vị vải ngọt dịu hòa cùng hương hoa hồng',
            ],
            'tra_oolong_nhan.jpg' => [
                'name' => 'Trà Oolong Nhãn',
                'description' => 'Trà oolong thơm nhẹ kết hợp nhãn tươi',
            ],
            'matcha_dau_mua_he.jpg' => [
                'name' => 'Matcha Dâu Mùa Hè',
                'description' => 'Matcha sữa mát lạnh cùng sốt dâu',
            ],
        ];

        foreach ($renames as $imageUrl => $productData) {
            DB::table('products')
                ->where('image_url', $imageUrl)
                ->update($productData);
        }
    }
};
