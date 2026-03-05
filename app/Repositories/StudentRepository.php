<?php

declare(strict_types=1);

namespace App\Repositories;

final class StudentRepository extends BaseRepository
{
    public function listBySection(int $sectionId): array
    {
        $sql = 'SELECT id, full_name, reg_no, email FROM students WHERE section_id = :sid AND active = 1 ORDER BY full_name ASC';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['sid' => $sectionId]);

        return $stmt->fetchAll();
    }

    public function existsInSection(int $studentId, int $sectionId): bool
    {
        $sql = 'SELECT id FROM students WHERE id = :id AND section_id = :sid AND active = 1 LIMIT 1';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['id' => $studentId, 'sid' => $sectionId]);

        return (bool) $stmt->fetch();
    }
}
