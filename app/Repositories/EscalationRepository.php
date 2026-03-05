<?php

declare(strict_types=1);

namespace App\Repositories;

final class EscalationRepository extends BaseRepository
{
    public function create(int $submissionId): void
    {
        $stmt = $this->db()->prepare('INSERT INTO ats_escalations (submission_id, escalated_at) VALUES (:sid, :at)');
        $stmt->execute([
            'sid' => $submissionId,
            'at' => now_utc(),
        ]);
    }
}
