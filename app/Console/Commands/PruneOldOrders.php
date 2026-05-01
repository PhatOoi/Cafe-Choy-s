<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneOldOrders extends Command
{
    protected $signature   = 'orders:prune-old';
    protected $description = 'Xóa đơn hàng cũ hơn 7 ngày để giữ cơ sở dữ liệu nhẹ';

    public function handle(): int
    {
        $cutoff = now()->subDays(7);

        // Lấy danh sách id đơn cũ hơn 7 ngày.
        // CASCADE trên payments, order_items, order_item_extras nên chỉ cần xóa orders là đủ.
        $deleted = DB::table('orders')
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Đã xóa {$deleted} đơn hàng cũ hơn 7 ngày (trước {$cutoff->format('Y-m-d H:i')}).");

        return Command::SUCCESS;
    }
}
