<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Model lưu tin nhắn chat giữa khách hàng và nhân viên hỗ trợ.
class ChatMessage extends Model
{
    // Các cột cho phép mass assignment khi tạo tin nhắn qua ChatController.
    protected $fillable = ['user_id', 'message', 'sender', 'replied_by', 'is_read'];

    // Khách hàng chủ sở hữu cuộc trò chuyện này.
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Nhân viên đã phản hồi tin nhắn này (nullable — chỉ có khi sender = 'staff').
    public function repliedBy()
    {
        return $this->belongsTo(User::class, 'replied_by');
    }
}
