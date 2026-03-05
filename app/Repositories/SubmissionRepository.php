<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\SubmissionStatus;

final class SubmissionRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        $sql = <<<'SQL'
SELECT
    sub.*,
    sess.section_id,
    sess.session_date,
    sec.name AS section_name,
    c.title AS course_title,
    c.lecturer_user_id
FROM attendance_submissions sub
JOIN sessions sess ON sess.id = sub.session_id
JOIN sections sec ON sec.id = sess.section_id
JOIN courses c ON c.id = sec.course_id
WHERE sub.id = :id
LIMIT 1
SQL;
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findBySessionId(int $sessionId): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM attendance_submissions WHERE session_id = :sid LIMIT 1');
        $stmt->execute(['sid' => $sessionId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(array $payload): int
    {
        $sql = <<<'SQL'
INSERT INTO attendance_submissions
(session_id, cr_user_id, teaching_declared_at, declaration_text, submitted_at, status, deadline_at, lecturer_user_id, signed_sheet_status)
VALUES
(:session_id, :cr_user_id, :teaching_declared_at, :declaration_text, :submitted_at, :status, :deadline_at, :lecturer_user_id, :signed_sheet_status)
SQL;

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($payload);

        return (int) $this->db()->lastInsertId();
    }

    public function updateStatusDecision(int $id, string $status, ?string $comment, ?string $decidedAt): void
    {
        $sql = <<<'SQL'
UPDATE attendance_submissions
SET status = :status,
    lecturer_comment = :comment,
    lecturer_decision_at = :decided_at,
    updated_at = :updated_at
WHERE id = :id
SQL;
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'status' => $status,
            'comment' => $comment,
            'decided_at' => $decidedAt,
            'updated_at' => now_utc(),
        ]);
    }

    public function setSignedSheetStatus(int $id, string $status): void
    {
        $stmt = $this->db()->prepare('UPDATE attendance_submissions SET signed_sheet_status = :s, updated_at = :u WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            's' => $status,
            'u' => now_utc(),
        ]);
    }

    public function pendingOverdue(string $now): array
    {
        $sql = <<<'SQL'
SELECT * FROM attendance_submissions
WHERE status = :pending
  AND deadline_at < :now
  AND id NOT IN (SELECT submission_id FROM escalations WHERE resolved_at IS NULL)
SQL;
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'pending' => SubmissionStatus::PENDING,
            'now' => $now,
        ]);

        return $stmt->fetchAll();
    }
}
