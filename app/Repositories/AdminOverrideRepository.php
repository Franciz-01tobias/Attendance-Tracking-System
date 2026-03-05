<?php

declare(strict_types=1);

namespace App\Repositories;

final class AdminOverrideRepository extends BaseRepository
{
    public function create(int $submissionId, int $adminId, string $action, string $reason): void
    {
        $stmt = $this->db()->prepare('INSERT INTO admin_overrides (submission_id, admin_user_id, action, reason, created_at) VALUES (:sid, :aid, :action, :reason, :created)');
        $stmt->execute([
            'sid' => $submissionId,
            'aid' => $adminId,
            'action' => $action,
            'reason' => $reason,
            'created' => now_utc(),
        ]);
    }
}
