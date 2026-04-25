<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Model lưu các khung giờ mà nhân viên đã đăng ký làm việc.
class WorkScheduleRegistration extends Model
{
    // Bảng này chỉ lưu created_at nên tắt timestamps mặc định của Laravel.
    public $timestamps = false;

    // Các cột cho phép mass assignment khi nhân viên tạo đăng ký giờ làm.
    protected $fillable = [
        'staff_id',
        'employment_type',
        'work_date',
        'start_time',
        'end_time',
        'shift_label',
        'note',
        'status',
        'approved_by',
        'approved_at',
        'closed_by',
        'closed_at',
    ];

    // Cast ngày làm việc để view/controller format thuận tiện hơn.
    protected $casts = [
        'work_date' => 'date',
        'approved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // Nhân viên đã tạo bản đăng ký này.
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    // Admin đã duyệt đăng ký giờ làm này.
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Admin đã khóa đăng ký giờ làm này để chốt bảng lương.
    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}