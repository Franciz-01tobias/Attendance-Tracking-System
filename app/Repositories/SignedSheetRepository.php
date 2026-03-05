<?php

declare(strict_types=1);

namespace App\Repositories;

final class SignedSheetRepository extends BaseRepository
{
    public function activeBySubmission(int $submissionId): ?array
    {
        $sql = 'SELECT * FROM signed_sheet_versions WHERE submission_id = :sid AND is_active = 1 ORDER BY version_no DESC LIMIT 1';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['sid' => $submissionId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function history(int $submissionId): array
    {
        $sql = 'SELECT * FROM signed_sheet_versions WHERE submission_id = :sid ORDER BY version_no DESC';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['sid' => $submissionId]);

        return $stmt->fetchAll();
    }

    public function nextVersionNo(int $submissionId): int
    {
        $stmt = $this->db()->prepare('SELECT COALESCE(MAX(version_no), 0) AS v FROM signed_sheet_versions WHERE submission_id = :sid');
        $stmt->execute(['sid' => $submissionId]);
        $row = $stmt->fetch();

        return ((int) ($row['v'] ?? 0)) + 1;
    }

    public function deactivateCurrent(int $submissionId): void
    {
        $sql = 'UPDATE signed_sheet_versions SET is_active = 0, replaced_at = :replaced_at WHERE submission_id = :sid AND is_active = 1';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'sid' => $submissionId,
            'replaced_at' => now_utc(),
        ]);
    }

    public function create(array $payload): int
    {
        $sql = <<<'SQL'
INSERT INTO signed_sheet_versions
(submission_id, version_no, is_active, uploaded_by_user_id, uploaded_at, original_name, mime_type, size_bytes, storage_path, sha256_hash, replaced_at)
VALUES
(:submission_id, :version_no, :is_active, :uploaded_by_user_id, :uploaded_at, :original_name, :mime_type, :size_bytes, :storage_path, :sha256_hash, :replaced_at)
SQL;
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($payload);

        return (int) $this->db()->lastInsertId();
    }
}
