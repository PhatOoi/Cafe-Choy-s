<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Các trường có thể gán hàng loạt khi tạo/cập nhật user.
    protected $fillable = ['name','email','password','role_id','is_active','phone','avatar_url'];

    // Các trường nhạy cảm không trả ra khi serialize model.
    protected $hidden   = ['password','remember_token'];

    // Cast kiểu dữ liệu để dùng thuận tiện trong code.
    protected $casts    = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean', // FIX: thiếu cast này
    ];

    // Vai trò của user trong hệ thống.
    public function role() { return $this->belongsTo(UserRole::class, 'role_id'); }

    // Các đơn khách hàng đã đặt.
    public function orders() { return $this->hasMany(Order::class); }

    // Các đơn đang được giao/được staff này phụ trách.
    public function assignedOrders() { return $this->hasMany(Order::class, 'assigned_staff_id'); }

    // Danh sách địa chỉ của user.
    public function addresses() { return $this->hasMany(Address::class); }

    // Helper kiểm tra nhanh vai trò để dùng trong controller/view.
    public function isAdmin(): bool  { return $this->role_id === 1; }
    public function isStaff(): bool  { return $this->role_id === 2; }
    public function isCustomer(): bool { return $this->role_id === 3; }

    // Accessor tên vai trò tiếng Việt.
    public function getRoleNameAttribute(): string {
        return match($this->role_id) { 1=>'Admin', 2=>'Nhân viên', 3=>'Khách hàng', default=>'?' };
    }

    // Accessor màu badge tương ứng vai trò để UI tái sử dụng.
    public function getRoleBadgeColorAttribute(): string {
        return match($this->role_id) { 1=>'danger', 2=>'warning', 3=>'success', default=>'secondary' };
    }
}
