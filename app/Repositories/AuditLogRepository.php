<?php

declare(strict_types=1);

namespace App\Repositories;

final class AuditLogRepository extends BaseRepository
{
    public function record(
        int $actorUserId,
        string $entityType,
        int $entityId,
        string $action,
        ?array $before,
        ?array $after,
        string $ip,
        string $userAgent
    ): void {
        $sql = <<<'SQL'
INSERT INTO ats_audit_logs
(actor_user_id, entity_type, entity_id, action, before_json, after_json, ip, user_agent, created_at)
VALUES
(:actor_user_id, :entity_type, :entity_id, :action, :before_json, :after_json, :ip, :user_agent, :created_at)
SQL;
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'actor_user_id' => $actorUserId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'before_json' => $before ? json_encode($before) : null,
            'after_json' => $after ? json_encode($after) : null,
            'ip' => $ip,
            'user_agent' => substr($userAgent, 0, 255),
            'created_at' => now_utc(),
        ]);
    }
}
