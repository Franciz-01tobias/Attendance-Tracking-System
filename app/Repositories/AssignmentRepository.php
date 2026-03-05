<?php

declare(strict_types=1);

namespace App\Repositories;

final class AssignmentRepository extends BaseRepository
{
    public function isCrAssigned(int $crUserId, int $sectionId, string $sessionDate): bool
    {
        $sql = <<<'SQL'
SELECT id
FROM ats_cr_assignments
WHERE cr_user_id = :uid
  AND section_id = :sid
  AND active = 1
  AND (starts_on IS NULL OR starts_on <= :d)
  AND (ends_on IS NULL OR ends_on >= :d)
LIMIT 1
SQL;
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'uid' => $crUserId,
            'sid' => $sectionId,
            'd' => $sessionDate,
        ]);

        return (bool) $stmt->fetch();
    }

    public function isCrAssignedForSession(int $crUserId, array $session): bool
    {
        $sql = <<<'SQL'
SELECT id
FROM ats_cr_assignments
WHERE cr_user_id = :uid
  AND active = 1
  AND (starts_on IS NULL OR starts_on <= :session_date_from)
  AND (ends_on IS NULL OR ends_on >= :session_date_to)
  AND (
      (slot_id IS NOT NULL AND slot_id = :slot_id)
      OR (
          slot_id IS NULL
          AND course_id IS NOT NULL
          AND course_id = :course_id
          AND (stage_no IS NULL OR stage_no = :stage_no)
          AND (qualification_level_id IS NULL OR qualification_level_id = :qualification_level_id)
          AND (semester_id IS NULL OR semester_id = :semester_id)
      )
      OR (
          section_id IS NOT NULL
          AND :session_section_id_filter IS NOT NULL
          AND section_id = :session_section_id_value
      )
  )
LIMIT 1
SQL;

        $slotId = $this->toNullableInt($session['slot_id'] ?? null);
        $courseId = $this->toNullableInt($session['course_id'] ?? null);
        $stageNo = $this->toNullableInt($session['stage_no'] ?? null);
        $qualificationLevelId = $this->toNullableInt($session['qualification_level_id'] ?? null);
        $semesterId = $this->toNullableInt($session['semester_id'] ?? null);
        $sectionId = $this->toNullableInt($session['section_id'] ?? null);
        $sessionDate = (string) ($session['session_date'] ?? '');

        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'uid' => $crUserId,
            'session_date_from' => $sessionDate,
            'session_date_to' => $sessionDate,
            'slot_id' => $slotId,
            'course_id' => $courseId,
            'stage_no' => $stageNo,
            'qualification_level_id' => $qualificationLevelId,
            'semester_id' => $semesterId,
            'session_section_id_filter' => $sectionId,
            'session_section_id_value' => $sectionId,
        ]);

        return (bool) $stmt->fetch();
    }

    private function toNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
