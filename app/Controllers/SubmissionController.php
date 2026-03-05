<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Services\AttendanceSubmissionService;
use App\Services\SubmissionItemService;

final class SubmissionController
{
    public function create(Request $request, array $params): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::json(['ok' => false, 'message' => 'Unauthenticated'], 401);
        }

        try {
            $sessionId = (int) ($params['id'] ?? 0);
            $declaration = (string) $request->input('declaration_text', 'Lecturer taught this session.');
            $jsonItems = (string) $request->input('items_json', '[]');
            $items = json_decode($jsonItems, true, 512, JSON_THROW_ON_ERROR);

            $submissionId = (new AttendanceSubmissionService())->submit($sessionId, $user, $declaration, $items, $request);
            if ($request->expectsJson()) {
                Response::json(['ok' => true, 'submission_id' => $submissionId], 201);
            }

            $_SESSION['flash_success'] = 'Attendance submitted successfully.';
            Response::redirect('/sessions/' . $sessionId);
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                Response::json(['ok' => false, 'message' => $e->getMessage()], 422);
            }

            $_SESSION['flash_error'] = $e->getMessage();
            Response::redirect('/sessions/' . (int) ($params['id'] ?? 0));
        }
    }

    public function updateItem(Request $request, array $params): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::json(['ok' => false, 'message' => 'Unauthenticated'], 401);
        }

        try {
            $submissionId = (int) ($params['id'] ?? 0);
            $itemId = (int) ($params['itemId'] ?? 0);
            $status = (string) $request->input('status', '');
            $note = $request->input('note');

            (new SubmissionItemService())->updateItem($submissionId, $itemId, $user, $status, is_string($note) ? $note : null, $request);
            Response::json(['ok' => true]);
        } catch (\Throwable $e) {
            Response::json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
