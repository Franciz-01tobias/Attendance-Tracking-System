<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditLogRepository;
use App\Repositories\EscalationRepository;
use App\Repositories\SubmissionRepository;

final class EscalationService
{
    public function __construct(
        private readonly SubmissionRepository $submissions = new SubmissionRepository(),
        private readonly EscalationRepository $escalations = new EscalationRepository(),
        private readonly AuditLogRepository $audit = new AuditLogRepository(),
    ) {
    }

    public function run(): array
    {
        $overdue = $this->submissions->pendingOverdue(now_utc());
        $count = 0;

        foreach ($overdue as $submission) {
            $this->escalations->create((int) $submission['id']);
            $this->audit->record(
                actorUserId: 1,
                entityType: 'attendance_submission',
                entityId: (int) $submission['id'],
                action: 'escalated_overdue',
                before: ['status' => $submission['status']],
                after: ['status' => $submission['status']],
                ip: '127.0.0.1',
                userAgent: 'cron/escalation',
            );
            $count++;
        }

        return ['overdue_found' => count($overdue), 'escalated' => $count];
    }
}
