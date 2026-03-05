<?php

declare(strict_types=1);

namespace App\Repositories;

final class AssignmentRepository extends BaseRepository
{
    public function isCrAssigned(int $crUserId, int $sectionId, string $sessionDate): bool
    {
        $sql = <<<'SQL'
SELECT id
FROM cr_assignments
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
}
