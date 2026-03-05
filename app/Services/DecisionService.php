<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Request;
use App\Enums\SubmissionStatus;
use App\Policies\SubmissionPolicy;
use App\Repositories\AuditLogRepository;
use App\Repositories\SignedSheetRepository;
use App\Repositories\SubmissionRepository;
use RuntimeException;

final class DecisionService
{
    public function __construct(
        private readonly SubmissionRepository $submissions = new SubmissionRepository(),
        private readonly SignedSheetRepository $sheets = new SignedSheetRepository(),
        private readonly SubmissionPolicy $policy = new SubmissionPolicy(),
        private readonly AuditLogRepository $audit = new AuditLogRepository(),
    ) {
    }

    public function approve(int $submissionId, array $user, Request $request): void
    {
        $submission = $this->submissions->findById($submissionId);
        if (!$submission) {
            throw new RuntimeException('Submission not found');
        }

        if (!$this->policy->canEditItems($user, $submission)) {
            throw new RuntimeException('Forbidden');
        }

        if (!$this->sheets->activeBySubmission($submissionId)) {
            throw new RuntimeException('Cannot approve without active signed sheet');
        }

        $before = $submission;
        $this->submissions->updateStatusDecision($submissionId, SubmissionStatus::APPROVED, null, now_utc());
        $after = $this->submissions->findById($submissionId);

        $this->audit->record((int) $user['id'], 'attendance_submission', $submissionId, 'approved', $before, $after, $request->ip(), $request->userAgent());
    }

    public function reject(int $submissionId, array $user, string $comment, Request $request): void
    {
        $comment = trim($comment);
        if ($comment === '') {
            throw new RuntimeException('Rejection comment is required');
        }

        $submission = $this->submissions->findById($submissionId);
        if (!$submission) {
            throw new RuntimeException('Submission not found');
        }

        if (!$this->policy->canEditItems($user, $submission)) {
            throw new RuntimeException('Forbidden');
        }

        $before = $submission;
        $this->submissions->updateStatusDecision($submissionId, SubmissionStatus::REJECTED, $comment, now_utc());
        $after = $this->submissions->findById($submissionId);

        $this->audit->record((int) $user['id'], 'attendance_submission', $submissionId, 'rejected', $before, $after, $request->ip(), $request->userAgent());
    }
}
