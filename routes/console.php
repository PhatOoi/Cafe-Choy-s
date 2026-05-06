<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\ChatMessage;

// File định nghĩa các lệnh Artisan kiểu closure dành cho nhu cầu đơn giản.
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tự động xóa tin nhắn chat cũ hơn 1 ngày mỗi đêm lúc nửa đêm.
Schedule::call(function () {
    ChatMessage::where('created_at', '<', now()->subDay())->delete();
})->daily();
