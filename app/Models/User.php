<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name','email','password','role_id','is_active','phone','avatar_url'];
    protected $hidden   = ['password','remember_token'];
    protected $casts    = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean', // FIX: thiếu cast này
    ];

    public function role() { return $this->belongsTo(UserRole::class, 'role_id'); }
    public function orders() { return $this->hasMany(Order::class); }
    public function assignedOrders() { return $this->hasMany(Order::class, 'assigned_staff_id'); }
    public function addresses() { return $this->hasMany(Address::class); }

    public function isAdmin(): bool  { return $this->role_id === 1; }
    public function isStaff(): bool  { return $this->role_id === 2; }
    public function isCustomer(): bool { return $this->role_id === 3; }

    public function getRoleNameAttribute(): string {
        return match($this->role_id) { 1=>'Admin', 2=>'Nhân viên', 3=>'Khách hàng', default=>'?' };
    }
    public function getRoleBadgeColorAttribute(): string {
        return match($this->role_id) { 1=>'danger', 2=>'warning', 3=>'success', default=>'secondary' };
    }
}
