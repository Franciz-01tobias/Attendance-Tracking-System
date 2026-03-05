<?php

declare(strict_types=1);

namespace App\Repositories;

final class SessionRepository extends BaseRepository
{
    public function findById(int $sessionId): ?array
    {
        $sql = <<<'SQL'
SELECT
    s.*,
    sec.course_id,
    sec.name AS section_name,
    c.title AS course_title,
    c.code AS course_code,
    c.lecturer_user_id
FROM sessions s
JOIN sections sec ON sec.id = s.section_id
JOIN courses c ON c.id = sec.course_id
WHERE s.id = :id
LIMIT 1
SQL;

        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['id' => $sessionId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function listForRole(array $user): array
    {
        if ($user['role'] === 'admin') {
            $sql = <<<'SQL'
SELECT s.id, s.session_date, s.starts_at, s.ends_at, s.status, sec.name AS section_name, c.title AS course_title
FROM sessions s
JOIN sections sec ON sec.id = s.section_id
JOIN courses c ON c.id = sec.course_id
ORDER BY s.session_date DESC, s.starts_at DESC
LIMIT 50
SQL;
            return $this->db()->query($sql)->fetchAll();
        }

        if ($user['role'] === 'lecturer') {
            $sql = <<<'SQL'
SELECT s.id, s.session_date, s.starts_at, s.ends_at, s.status, sec.name AS section_name, c.title AS course_title
FROM sessions s
JOIN sections sec ON sec.id = s.section_id
JOIN courses c ON c.id = sec.course_id
WHERE c.lecturer_user_id = :uid
ORDER BY s.session_date DESC, s.starts_at DESC
LIMIT 50
SQL;
            $stmt = $this->db()->prepare($sql);
            $stmt->execute(['uid' => $user['id']]);
            return $stmt->fetchAll();
        }

        $sql = <<<'SQL'
SELECT s.id, s.session_date, s.starts_at, s.ends_at, s.status, sec.name AS section_name, c.title AS course_title
FROM sessions s
JOIN sections sec ON sec.id = s.section_id
JOIN courses c ON c.id = sec.course_id
JOIN cr_assignments ca ON ca.section_id = sec.id
WHERE ca.cr_user_id = :uid
  AND ca.active = 1
  AND (ca.starts_on IS NULL OR ca.starts_on <= s.session_date)
  AND (ca.ends_on IS NULL OR ca.ends_on >= s.session_date)
ORDER BY s.session_date DESC, s.starts_at DESC
LIMIT 50
SQL;
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['uid' => $user['id']]);

        return $stmt->fetchAll();
    }
}
