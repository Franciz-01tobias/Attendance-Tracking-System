<?php

declare(strict_types=1);

namespace App\Sync;

interface MirrorSyncServiceInterface
{
    public function run(): array;
}
