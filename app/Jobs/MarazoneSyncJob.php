<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Sync\MirrorSyncService;
use App\Sync\SessionProjectionService;

final class MarazoneSyncJob
{
    public function run(): array
    {
        $sync = (new MirrorSyncService())->run();
        if (!($sync['ok'] ?? false)) {
            return $sync;
        }

        $projection = (new SessionProjectionService())->projectForDate((new \DateTimeImmutable('now', app_timezone()))->format('Y-m-d'));

        return [
            'ok' => true,
            'sync' => $sync,
            'projection' => $projection,
        ];
    }
}
