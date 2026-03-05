<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Request;
use App\Enums\AttendanceItemStatus;
use App\Policies\SubmissionPolicy;
use App\Repositories\AttendanceItemRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\SubmissionRepository;
use RuntimeException;

final class SubmissionItemService
{
    public function __construct(
        private readonly SubmissionRepository $submissions = new SubmissionRepository(),
        private readonly AttendanceItemRepository $items = new AttendanceItemRepository(),
        private readonly SubmissionPolicy $policy = new SubmissionPolicy(),
        private readonly AuditLogRepository $audit = new AuditLogRepository(),
    ) {
    }

    public function updateItem(int $submissionId, int $itemId, array $user, string $status, ?string $note, Request $request): void
    {
        if (!in_array($status, AttendanceItemStatus::all(), true)) {
            throw new RuntimeException('Invalid attendance status');
        }

        $submission = $this->submissions->findById($submissionId);
        if (!$submission) {
            throw new RuntimeException('Submission not found');
        }

        if (!$this->policy->canEditItems($user, $submission)) {
            throw new RuntimeException('Forbidden');
        }

        $before = ['id' => $itemId];
        $this->items->updateItem($itemId, $status, $note, (int) $user['id']);

        $this->audit->record(
            actorUserId: (int) $user['id'],
            entityType: 'attendance_item',
            entityId: $itemId,
            action: 'updated',
            before: $before,
            after: ['status' => $status, 'note' => $note],
            ip: $request->ip(),
            userAgent: $request->userAgent(),
        );
    }
}
