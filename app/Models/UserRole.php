<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Model đại diện cho vai trò người dùng: 1=Admin, 2=Staff, 3=Customer.
class UserRole extends Model
{
    use HasFactory;

    // Tên bảng vai trò trong database.
    protected $table = 'user_roles';

    // Bảng này không dùng timestamps tự động.
    public $timestamps = false;

    // Tên vai trò và mô tả có thể được tạo/cập nhật qua seeder.
    protected $fillable = ['name', 'description'];

    // Một vai trò có thể được gán cho nhiều user trong hệ thống.
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}