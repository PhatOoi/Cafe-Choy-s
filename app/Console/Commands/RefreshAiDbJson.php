<?php

namespace App\Console\Commands;

use App\Support\AiMenuSnapshotService;
use Illuminate\Console\Command;

class RefreshAiDbJson extends Command
{
    protected $signature = 'ai:refresh-db-json';

    protected $description = 'Regenerate storage/app/ai/DB.json from current menu data';

    public function handle(AiMenuSnapshotService $snapshotService): int
    {
        try {
            $payload = $snapshotService->refresh();

            $this->info('Da cap nhat DB.json thanh cong.');
            $this->line('Duong dan: ' . $snapshotService->absolutePath());
            $this->line('So category: ' . count($payload['categories'] ?? []));
            $this->line('So product: ' . count($payload['products'] ?? []));
            $this->line('So size: ' . count($payload['sizes'] ?? []));
            $this->line('So extra: ' . count($payload['extras'] ?? []));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Cap nhat DB.json that bai: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
