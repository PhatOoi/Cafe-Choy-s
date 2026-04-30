<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Các trường có thể gán hàng loạt khi tạo/cập nhật user.
    protected $fillable = ['name','email','password','role_id','employment_type','citizen_id','is_active','phone','avatar_url','loyalty_points'];

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

    // Các khung giờ làm việc mà nhân viên đã đăng ký.
    public function workScheduleRegistrations() { return $this->hasMany(WorkScheduleRegistration::class, 'staff_id'); }

    // Các giờ tăng ca đã đăng ký.
    public function overtimes() { return $this->hasMany(Overtime::class, 'staff_id'); }

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

    // Accessor tên loại nhân viên để hiển thị bảng full-time/part-time rõ ràng hơn.
    public function getEmploymentTypeLabelAttribute(): ?string {
        return match($this->employment_type) {
            'full_time' => 'Full-time',
            'part_time' => 'Part-time',
            default => null,
        };
    }
}
