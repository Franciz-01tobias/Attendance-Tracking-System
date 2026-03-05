<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Services\SignedSheetService;

final class SignedSheetController
{
    public function upload(Request $request, array $params): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::json(['ok' => false, 'message' => 'Unauthenticated'], 401);
        }

        try {
            $submissionId = (int) ($params['id'] ?? 0);
            $file = $request->files['signed_sheet'] ?? null;
            if (!is_array($file)) {
                throw new \RuntimeException('signed_sheet file is required');
            }

            $result = (new SignedSheetService())->upload($submissionId, $user, $file, $request);
            if ($request->expectsJson()) {
                Response::json(['ok' => true, 'signed_sheet' => $result]);
            }

            $_SESSION['flash_success'] = 'Signed sheet uploaded.';
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

    public function download(Request $request, array $params): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::json(['ok' => false, 'message' => 'Unauthenticated'], 401);
        }

        try {
            $submissionId = (int) ($params['id'] ?? 0);
            $active = (new SignedSheetService())->getActive($submissionId, $user);
            if (!$active) {
                Response::json(['ok' => false, 'message' => 'No active signed sheet'], 404);
            }

            Response::fileDownload(
                absolutePath: (string) $active['storage_path'],
                downloadName: (string) $active['original_name'],
                mime: (string) $active['mime_type']
            );
        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function history(Request $request, array $params): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::json(['ok' => false, 'message' => 'Unauthenticated'], 401);
        }

        try {
            $submissionId = (int) ($params['id'] ?? 0);
            $history = (new SignedSheetService())->history($submissionId, $user);
            Response::json(['ok' => true, 'history' => $history]);
        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
