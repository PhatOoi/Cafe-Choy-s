<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;

    // Một vai trò có thể được gán cho nhiều user trong hệ thống.
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}