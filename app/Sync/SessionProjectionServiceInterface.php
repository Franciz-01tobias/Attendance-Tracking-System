<?php

declare(strict_types=1);

namespace App\Sync;

interface SessionProjectionServiceInterface
{
    public function projectForDate(string $date): array;
}
