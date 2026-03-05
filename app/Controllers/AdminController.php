<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Services\AdminOverrideService;

final class AdminController
{
    public function override(Request $request, array $params): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::json(['ok' => false, 'message' => 'Unauthenticated'], 401);
        }

        try {
            $submissionId = (int) ($params['id'] ?? 0);
            $action = (string) $request->input('action', '');
            $reason = (string) $request->input('reason', '');

            (new AdminOverrideService())->override($submissionId, $user, $action, $reason, $request);
            if ($request->expectsJson()) {
                Response::json(['ok' => true]);
            }

            $_SESSION['flash_success'] = 'Admin override applied.';
            $submission = (new \App\Repositories\SubmissionRepository())->findById($submissionId);
            Response::redirect('/sessions/' . (int) ($submission['session_id'] ?? 0));
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                Response::json(['ok' => false, 'message' => $e->getMessage()], 422);
            }

            $_SESSION['flash_error'] = $e->getMessage();
            $submissionId = (int) ($params['id'] ?? 0);
            $submission = (new \App\Repositories\SubmissionRepository())->findById($submissionId);
            Response::redirect('/sessions/' . (int) ($submission['session_id'] ?? 0));
        }
    }
}
