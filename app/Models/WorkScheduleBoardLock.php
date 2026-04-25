<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Model lưu trạng thái khóa bảng đăng ký giờ làm theo từng tuần.
class WorkScheduleBoardLock extends Model
{
    // Bảng có cả created_at và updated_at nên dùng timestamps mặc định.

    // Các cột cho phép gán hàng loạt khi tạo bản ghi khóa bảng tuần.
    protected $fillable = [
        'week_start',
        'week_end',
        'locked_by',
        'locked_at',
    ];

    // Cast ngày và thời điểm khóa để xử lý hiển thị thuận tiện.
    protected $casts = [
        'week_start' => 'date',
        'week_end' => 'date',
        'locked_at' => 'datetime',
    ];

    // Admin đã thực hiện thao tác khóa bảng tuần.
    public function locker()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
}