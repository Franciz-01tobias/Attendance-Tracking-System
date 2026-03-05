<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\EscalationService;

final class RunEscalationJob
{
    public function run(): array
    {
        return (new EscalationService())->run();
    }
}
