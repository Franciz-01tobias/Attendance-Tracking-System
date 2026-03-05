<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Services\DecisionService;

final class DecisionController
{
    public function approve(Request $request, array $params): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::json(['ok' => false, 'message' => 'Unauthenticated'], 401);
        }

        try {
            $submissionId = (int) ($params['id'] ?? 0);
            (new DecisionService())->approve($submissionId, $user, $request);
            if ($request->expectsJson()) {
                Response::json(['ok' => true]);
            }

            $_SESSION['flash_success'] = 'Submission approved.';
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

    public function reject(Request $request, array $params): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::json(['ok' => false, 'message' => 'Unauthenticated'], 401);
        }

        try {
            $submissionId = (int) ($params['id'] ?? 0);
            $comment = (string) $request->input('lecturer_comment', '');
            (new DecisionService())->reject($submissionId, $user, $comment, $request);
            if ($request->expectsJson()) {
                Response::json(['ok' => true]);
            }

            $_SESSION['flash_success'] = 'Submission rejected.';
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
