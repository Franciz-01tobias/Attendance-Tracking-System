<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Request;
use App\Enums\SignedSheetStatus;
use App\Enums\SubmissionStatus;
use App\Repositories\AssignmentRepository;
use App\Repositories\AttendanceItemRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\SessionRepository;
use App\Repositories\StudentRepository;
use App\Repositories\SubmissionRepository;
use RuntimeException;

final class AttendanceSubmissionService
{
    public function __construct(
        private readonly SessionRepository $sessions = new SessionRepository(),
        private readonly AssignmentRepository $assignments = new AssignmentRepository(),
        private readonly SubmissionRepository $submissions = new SubmissionRepository(),
        private readonly AttendanceItemRepository $items = new AttendanceItemRepository(),
        private readonly StudentRepository $students = new StudentRepository(),
        private readonly AuditLogRepository $audit = new AuditLogRepository(),
    ) {
    }

    public function submit(int $sessionId, array $user, string $declaration, array $items, Request $request): int
    {
        if ($user['role'] !== 'cr') {
            throw new RuntimeException('Only CR can submit attendance');
        }

        $session = $this->sessions->findById($sessionId);
        if (!$session) {
            throw new RuntimeException('Session not found');
        }

        $isAssigned = $this->assignments->isCrAssignedForSession((int) $user['id'], $session);
        if (!$isAssigned) {
            throw new RuntimeException('CR is not assigned to this class/session');
        }

        if ($this->submissions->findBySessionId($sessionId)) {
            throw new RuntimeException('Submission already exists for this session');
        }

        $rosterStudents = $this->students->listForSession($session);
        $expectedCount = count($rosterStudents);
        if ($expectedCount === 0) {
            throw new RuntimeException('No active students found for this assigned class');
        }

        if (count($items) !== $expectedCount) {
            throw new RuntimeException('Attendance must include every student in the class roster');
        }

        $expectedIds = array_map(static fn(array $s): int => (int) $s['id'], $rosterStudents);
        sort($expectedIds);

        $providedIds = array_map(static fn(array $i): int => (int) ($i['student_id'] ?? 0), $items);
        sort($providedIds);

        if ($expectedIds !== $providedIds) {
            throw new RuntimeException('Attendance list does not match assigned class roster');
        }

        $lecturerUserId = $this->nullableInt($session['lecturer_user_id'] ?? null);
        $lecturerMarazoneUserId = $this->nullableInt($session['lecturer_marazone_user_id'] ?? null);

        $db = \App\Core\Database::default();
        $db->beginTransaction();
        try {
            $submittedAt = now_utc();
            $deadline = (new \DateTimeImmutable($submittedAt, new \DateTimeZone('UTC')))
                ->modify('+' . (int) config('security.approval_sla_hours', 24) . ' hours')
                ->format('Y-m-d H:i:s');

            $submissionId = $this->submissions->create([
                'session_id' => $sessionId,
                'cr_user_id' => (int) $user['id'],
                'teaching_declared_at' => $submittedAt,
                'declaration_text' => $declaration,
                'submitted_at' => $submittedAt,
                'status' => SubmissionStatus::PENDING,
                'deadline_at' => $deadline,
                'lecturer_user_id' => $lecturerUserId,
                'lecturer_marazone_user_id' => $lecturerMarazoneUserId,
                'signed_sheet_status' => SignedSheetStatus::MISSING,
            ]);

            $this->items->bulkInsert($submissionId, $items, (int) $user['id']);

            $this->audit->record(
                actorUserId: (int) $user['id'],
                entityType: 'attendance_submission',
                entityId: $submissionId,
                action: 'submitted',
                before: null,
                after: ['session_id' => $sessionId, 'items_count' => count($items)],
                ip: $request->ip(),
                userAgent: $request->userAgent(),
            );

            $db->commit();
            return $submissionId;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
