<?php

namespace App\Observers;

use App\Support\AiMenuSnapshotService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AiMenuSnapshotObserver
{
    public function saved(Model $model): void
    {
        $this->refreshSnapshot();
    }

    public function deleted(Model $model): void
    {
        $this->refreshSnapshot();
    }

    private function refreshSnapshot(): void
    {
        try {
            app(AiMenuSnapshotService::class)->refresh();
        } catch (\Throwable $e) {
            Log::warning('Khong the cap nhat AI DB.json sau thay doi menu', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
