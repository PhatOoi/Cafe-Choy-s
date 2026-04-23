<?php

namespace App\Console\Commands;

use App\Models\ChatMessage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

// Artisan command dọn dẹp tin nhắn chat cũ của khách không hoạt động quá 30 phút.
// Chạy mỗi phút qua scheduler trong Kernel.php — lệnh: php artisan chat:prune-old
class PruneOldChats extends Command
{
    // Tên lệnh Artisan để gọi từ CLI hoặc scheduler.
    protected $signature = 'chat:prune-old';

    // Mô tả ngắn hiện ra khi chạy php artisan list.
    protected $description = 'Xóa chat của khách không hoạt động trong 30 phút';

    public function handle()
    {
        // Xóa messages của user nếu không hoạt động trong 30 phút
        $threshold = now()->subMinutes(30);

        $users = User::where('role_id', 3)  // Chỉ khách hàng
            ->where('last_chat_activity_at', '!=', null)
            ->where('last_chat_activity_at', '<', $threshold)
            ->get();

        $deletedCount = 0;
        foreach ($users as $user) {
            $count = ChatMessage::where('user_id', $user->id)->delete();
            $deletedCount += $count;
            // Reset timestamp
            $user->update(['last_chat_activity_at' => null]);
        }

        $this->info("Đã xóa $deletedCount tin nhắn từ " . $users->count() . ' khách không hoạt động.');
    }
}
