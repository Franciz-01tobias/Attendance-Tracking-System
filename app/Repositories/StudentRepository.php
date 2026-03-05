<?php

declare(strict_types=1);

namespace App\Repositories;

final class StudentRepository extends BaseRepository
{
    public function listForSession(array $session): array
    {
        $courseId = $this->toNullableInt($session['course_id'] ?? null);
        $stageNo = $this->toNullableInt($session['stage_no'] ?? null);
        $qualificationLevelId = $this->toNullableInt($session['qualification_level_id'] ?? null);

        if ($courseId !== null) {
            return $this->listFromMarazoneStudents($courseId, $stageNo, $qualificationLevelId);
        }

        $sectionId = $this->toNullableInt($session['section_id'] ?? null);
        if ($sectionId !== null) {
            return $this->listBySection($sectionId);
        }

        return [];
    }

    public function listBySection(int $sectionId): array
    {
        $sql = 'SELECT id, full_name, reg_no, email FROM ats_students WHERE section_id = :sid AND active = 1 ORDER BY full_name ASC';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['sid' => $sectionId]);

        return $stmt->fetchAll();
    }

    public function existsInSection(int $studentId, int $sectionId): bool
    {
        $sql = 'SELECT id FROM ats_students WHERE id = :id AND section_id = :sid AND active = 1 LIMIT 1';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['id' => $studentId, 'sid' => $sectionId]);

        return (bool) $stmt->fetch();
    }

    private function listFromMarazoneStudents(int $courseId, ?int $stageNo, ?int $qualificationLevelId): array
    {
        $sql = <<<'SQL'
SELECT
    s.student_id AS id,
    COALESCE(NULLIF(CONCAT_WS(' ', u.first_name, NULLIF(u.middle_name, ''), u.last_name), ''), CONCAT('Student ', s.student_id)) AS full_name,
    COALESCE(s.admission_no, s.form4_reg_no, CAST(s.student_id AS CHAR)) AS reg_no,
    u.email
FROM students s
JOIN users u ON u.user_id = s.user_id
WHERE s.status = 'ACTIVE'
  AND u.status = 'ACTIVE'
  AND s.course_id = :course_id
  AND (:stage_no_filter IS NULL OR s.current_stage_no = :stage_no_value)
  AND s.qualification_level_id <=> :qualification_level_id
ORDER BY full_name ASC
SQL;

        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'course_id' => $courseId,
            'stage_no_filter' => $stageNo,
            'stage_no_value' => $stageNo,
            'qualification_level_id' => $qualificationLevelId,
        ]);

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
