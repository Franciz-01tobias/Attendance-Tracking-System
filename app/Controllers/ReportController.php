<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\ReportRepository;

final class ReportController
{
    public function attendance(Request $request, array $params): void
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'admin') {
            Response::json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        Response::json(['ok' => true, 'data' => (new ReportRepository())->attendanceSummary()]);
    }

    public function turnaround(Request $request, array $params): void
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'admin') {
            Response::json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        Response::json(['ok' => true, 'data' => (new ReportRepository())->turnaroundSummary()]);
    }

    public function escalations(Request $request, array $params): void
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'admin') {
            Response::json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        Response::json(['ok' => true, 'data' => (new ReportRepository())->escalationSummary()]);
    }
}
