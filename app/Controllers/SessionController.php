<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\AssignmentRepository;
use App\Repositories\AttendanceItemRepository;
use App\Repositories\SessionRepository;
use App\Repositories\SignedSheetRepository;
use App\Repositories\StudentRepository;
use App\Repositories\SubmissionRepository;

final class SessionController
{
    public function show(Request $request, array $params): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::redirect('/login');
        }

        $sessionId = (int) ($params['id'] ?? 0);
        $session = (new SessionRepository())->findById($sessionId);

        if (!$session) {
            Response::json(['ok' => false, 'message' => 'Session not found'], 404);
        }

        if ($user['role'] === 'lecturer' && (int) $session['lecturer_user_id'] !== (int) $user['id']) {
            Response::json(['ok' => false, 'message' => 'Forbidden'], 403);
        }
        if ($user['role'] === 'cr') {
            $assigned = (new AssignmentRepository())->isCrAssigned(
                (int) $user['id'],
                (int) $session['section_id'],
                (string) $session['session_date']
            );
            if (!$assigned) {
                Response::json(['ok' => false, 'message' => 'Forbidden'], 403);
            }
        }

        $submissionRepo = new SubmissionRepository();
        $submission = $submissionRepo->findBySessionId($sessionId);
        $students = (new StudentRepository())->listBySection((int) $session['section_id']);
        $items = $submission ? (new AttendanceItemRepository())->listBySubmission((int) $submission['id']) : [];
        $signedSheet = $submission ? (new SignedSheetRepository())->activeBySubmission((int) $submission['id']) : null;

        if ($request->expectsJson()) {
            Response::json([
                'ok' => true,
                'session' => $session,
                'submission' => $submission,
                'students' => $students,
                'items' => $items,
                'signed_sheet' => $signedSheet,
            ]);
        }

        Response::view('sessions/show', [
            'title' => 'Session Workspace',
            'user' => $user,
            'session' => $session,
            'submission' => $submission,
            'students' => $students,
            'items' => $items,
            'signedSheet' => $signedSheet,
            'csrf' => Csrf::token(),
        ]);
    }
}
