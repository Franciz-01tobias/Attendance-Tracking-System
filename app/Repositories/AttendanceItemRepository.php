<?php

declare(strict_types=1);

namespace App\Repositories;

final class AttendanceItemRepository extends BaseRepository
{
    public function bulkInsert(int $submissionId, array $items, int $updatedBy): void
    {
        $sql = 'INSERT INTO attendance_items (submission_id, student_id, status, note, updated_by, created_at, updated_at) VALUES (:sub_id, :student_id, :status, :note, :updated_by, :created_at, :updated_at)';
        $stmt = $this->db()->prepare($sql);

        foreach ($items as $item) {
            $stmt->execute([
                'sub_id' => $submissionId,
                'student_id' => (int) $item['student_id'],
                'status' => $item['status'],
                'note' => $item['note'] ?? null,
                'updated_by' => $updatedBy,
                'created_at' => now_utc(),
                'updated_at' => now_utc(),
            ]);
        }
    }

    public function listBySubmission(int $submissionId): array
    {
        $sql = <<<'SQL'
SELECT i.*, st.full_name, st.reg_no
FROM attendance_items i
JOIN students st ON st.id = i.student_id
WHERE i.submission_id = :id
ORDER BY st.full_name ASC
SQL;
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['id' => $submissionId]);

        return $stmt->fetchAll();
    }

    public function updateItem(int $itemId, string $status, ?string $note, int $updatedBy): void
    {
        $sql = 'UPDATE attendance_items SET status = :status, note = :note, updated_by = :updated_by, updated_at = :updated_at WHERE id = :id';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'id' => $itemId,
            'status' => $status,
            'note' => $note,
            'updated_by' => $updatedBy,
            'updated_at' => now_utc(),
        ]);
    }

    public function countBySubmission(int $submissionId): int
    {
        $stmt = $this->db()->prepare('SELECT COUNT(*) AS c FROM attendance_items WHERE submission_id = :id');
        $stmt->execute(['id' => $submissionId]);
        $row = $stmt->fetch();

        return (int) ($row['c'] ?? 0);
    }
}
