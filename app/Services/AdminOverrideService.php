<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Request;
use App\Enums\SubmissionStatus;
use App\Policies\SubmissionPolicy;
use App\Repositories\AdminOverrideRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\SubmissionRepository;
use RuntimeException;

final class AdminOverrideService
{
    public function __construct(
        private readonly SubmissionRepository $submissions = new SubmissionRepository(),
        private readonly AdminOverrideRepository $overrides = new AdminOverrideRepository(),
        private readonly SubmissionPolicy $policy = new SubmissionPolicy(),
        private readonly AuditLogRepository $audit = new AuditLogRepository(),
    ) {
    }

    public function override(int $submissionId, array $user, string $action, string $reason, Request $request): void
    {
        if (!$this->policy->canOverride($user)) {
            throw new RuntimeException('Forbidden');
        }

        $action = strtolower(trim($action));
        if (!in_array($action, ['approve', 'reject'], true)) {
            throw new RuntimeException('Invalid override action');
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw new RuntimeException('Override reason is required');
        }

        $submission = $this->submissions->findById($submissionId);
        if (!$submission) {
            throw new RuntimeException('Submission not found');
        }

        $before = $submission;
        $newStatus = $action === 'approve' ? SubmissionStatus::APPROVED : SubmissionStatus::REJECTED;

        $db = \App\Core\Database::default();
        $db->beginTransaction();
        try {
            $this->submissions->updateStatusDecision($submissionId, SubmissionStatus::OVERRIDDEN, $reason, now_utc());
            $this->overrides->create($submissionId, (int) $user['id'], $action, $reason);

            $after = $this->submissions->findById($submissionId);
            $this->audit->record((int) $user['id'], 'attendance_submission', $submissionId, 'admin_override_' . $newStatus, $before, $after, $request->ip(), $request->userAgent());
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
