<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Xóa chat của khách không hoạt động trong 30 phút, chạy mỗi phút
        $schedule->command('chat:prune-old')->everyMinute();

        // Xóa đơn hàng cũ hơn 7 ngày mỗi ngày lúc 00:05 để giữ DB nhẹ
        $schedule->command('orders:prune-old')->dailyAt('00:05');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
