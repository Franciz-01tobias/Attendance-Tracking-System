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
    COALESCE(c.course_name, ac.title, CONCAT('Course ', s.course_id)) AS course_title,
    COALESCE(c.course_code, ac.code, '') AS course_code,
    COALESCE(sec.name, CONCAT('Stage ', COALESCE(CAST(s.stage_no AS CHAR), '?'), ' / Semester ', COALESCE(CAST(s.semester_id AS CHAR), '?'))) AS section_name,
    s.room AS room_name,
    ac.lecturer_user_id AS lecturer_user_id
FROM ats_sessions s
LEFT JOIN courses c ON c.course_id = s.course_id
LEFT JOIN ats_sections sec ON sec.id = s.section_id
LEFT JOIN ats_courses ac ON ac.id = sec.course_id
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
SELECT
    s.id,
    s.session_date,
    s.starts_at,
    s.ends_at,
    s.status,
    COALESCE(c.course_name, ac.title, CONCAT('Course ', s.course_id)) AS course_title,
    COALESCE(sec.name, CONCAT('Stage ', COALESCE(CAST(s.stage_no AS CHAR), '?'), ' / Semester ', COALESCE(CAST(s.semester_id AS CHAR), '?'))) AS section_name
FROM ats_sessions s
LEFT JOIN courses c ON c.course_id = s.course_id
LEFT JOIN ats_sections sec ON sec.id = s.section_id
LEFT JOIN ats_courses ac ON ac.id = sec.course_id
ORDER BY s.session_date DESC, s.starts_at DESC
LIMIT 50
SQL;

            return $this->db()->query($sql)->fetchAll();
        }

        if ($user['role'] === 'lecturer') {
            $sql = <<<'SQL'
SELECT
    s.id,
    s.session_date,
    s.starts_at,
    s.ends_at,
    s.status,
    COALESCE(c.course_name, ac.title, CONCAT('Course ', s.course_id)) AS course_title,
    COALESCE(sec.name, CONCAT('Stage ', COALESCE(CAST(s.stage_no AS CHAR), '?'), ' / Semester ', COALESCE(CAST(s.semester_id AS CHAR), '?'))) AS section_name
FROM ats_sessions s
LEFT JOIN courses c ON c.course_id = s.course_id
LEFT JOIN ats_sections sec ON sec.id = s.section_id
LEFT JOIN ats_courses ac ON ac.id = sec.course_id
WHERE (
    (s.lecturer_marazone_user_id = :marazone_uid)
    OR (ac.lecturer_user_id = :uid)
)
ORDER BY s.session_date DESC, s.starts_at DESC
LIMIT 50
SQL;
            $stmt = $this->db()->prepare($sql);
            $stmt->execute([
                'uid' => (int) $user['id'],
                'marazone_uid' => $this->toNullableInt($user['marazone_user_id'] ?? null),
            ]);

            return $stmt->fetchAll();
        }

        $sql = <<<'SQL'
SELECT
    s.id,
    s.session_date,
    s.starts_at,
    s.ends_at,
    s.status,
    COALESCE(c.course_name, ac.title, CONCAT('Course ', s.course_id)) AS course_title,
    COALESCE(sec.name, CONCAT('Stage ', COALESCE(CAST(s.stage_no AS CHAR), '?'), ' / Semester ', COALESCE(CAST(s.semester_id AS CHAR), '?'))) AS section_name
FROM ats_sessions s
LEFT JOIN courses c ON c.course_id = s.course_id
LEFT JOIN ats_sections sec ON sec.id = s.section_id
LEFT JOIN ats_courses ac ON ac.id = sec.course_id
WHERE EXISTS (
    SELECT 1
    FROM ats_cr_assignments ca
    WHERE ca.cr_user_id = :uid
      AND ca.active = 1
      AND (ca.starts_on IS NULL OR ca.starts_on <= s.session_date)
      AND (ca.ends_on IS NULL OR ca.ends_on >= s.session_date)
      AND (
        (ca.slot_id IS NOT NULL AND ca.slot_id = s.slot_id)
        OR (
            ca.slot_id IS NULL
            AND ca.course_id IS NOT NULL
            AND ca.course_id = s.course_id
            AND (ca.stage_no IS NULL OR ca.stage_no = s.stage_no)
            AND (ca.qualification_level_id IS NULL OR ca.qualification_level_id = s.qualification_level_id)
            AND (ca.semester_id IS NULL OR ca.semester_id = s.semester_id)
        )
        OR (ca.section_id IS NOT NULL AND s.section_id IS NOT NULL AND ca.section_id = s.section_id)
      )
)
ORDER BY s.session_date DESC, s.starts_at DESC
LIMIT 50
SQL;
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['uid' => (int) $user['id']]);

        return $stmt->fetchAll();
    }

    private function toNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
