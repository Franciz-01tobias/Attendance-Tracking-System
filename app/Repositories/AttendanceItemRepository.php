<?php

declare(strict_types=1);

namespace App\Repositories;

final class AttendanceItemRepository extends BaseRepository
{
    public function bulkInsert(int $submissionId, array $items, int $updatedBy): void
    {
        $sql = 'INSERT INTO ats_attendance_items (submission_id, student_id, status, note, updated_by, created_at, updated_at) VALUES (:sub_id, :student_id, :status, :note, :updated_by, :created_at, :updated_at)';
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
SELECT
    i.*,
    COALESCE(NULLIF(CONCAT_WS(' ', mu.first_name, NULLIF(mu.middle_name, ''), mu.last_name), ''), ls.full_name, CONCAT('Student ', i.student_id)) AS full_name,
    COALESCE(ms.admission_no, ms.form4_reg_no, ls.reg_no, CAST(i.student_id AS CHAR)) AS reg_no
FROM ats_attendance_items i
LEFT JOIN students ms ON ms.student_id = i.student_id
LEFT JOIN users mu ON mu.user_id = ms.user_id
LEFT JOIN ats_students ls ON ls.id = i.student_id
WHERE i.submission_id = :id
ORDER BY full_name ASC
SQL;
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['id' => $submissionId]);

        return $stmt->fetchAll();
    }

    public function updateItem(int $itemId, string $status, ?string $note, int $updatedBy): void
    {
        $sql = 'UPDATE ats_attendance_items SET status = :status, note = :note, updated_by = :updated_by, updated_at = :updated_at WHERE id = :id';
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
        $stmt = $this->db()->prepare('SELECT COUNT(*) AS c FROM ats_attendance_items WHERE submission_id = :id');
        $stmt->execute(['id' => $submissionId]);
        $row = $stmt->fetch();

        return (int) ($row['c'] ?? 0);
    }
}
