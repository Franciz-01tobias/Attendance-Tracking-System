<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Request;
use App\Enums\SignedSheetStatus;
use App\Policies\SubmissionPolicy;
use App\Repositories\AuditLogRepository;
use App\Repositories\SignedSheetRepository;
use App\Repositories\SubmissionRepository;
use RuntimeException;

final class SignedSheetService
{
    public function __construct(
        private readonly SubmissionRepository $submissions = new SubmissionRepository(),
        private readonly SignedSheetRepository $sheets = new SignedSheetRepository(),
        private readonly SubmissionPolicy $policy = new SubmissionPolicy(),
        private readonly AuditLogRepository $audit = new AuditLogRepository(),
    ) {
    }

    public function upload(int $submissionId, array $user, array $file, Request $request): array
    {
        $submission = $this->submissions->findById($submissionId);
        if (!$submission) {
            throw new RuntimeException('Submission not found');
        }

        if (!$this->policy->canUploadSheet($user, $submission)) {
            throw new RuntimeException('Only assigned lecturer can upload while submission is pending');
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload failed');
        }

        $maxBytes = ((int) config('security.max_upload_size_mb', 10)) * 1024 * 1024;
        if ((int) ($file['size'] ?? 0) > $maxBytes) {
            throw new RuntimeException('File exceeds max size');
        }

        $allowedMime = config('security.allowed_upload_mime', []);
        $allowedExt = config('security.allowed_upload_ext', []);

        $tmpName = (string) ($file['tmp_name'] ?? '');
        $original = (string) ($file['name'] ?? 'signed-sheet');
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $mime = mime_content_type($tmpName) ?: 'application/octet-stream';

        if (!in_array($ext, $allowedExt, true) || !in_array($mime, $allowedMime, true)) {
            throw new RuntimeException('Invalid file type');
        }

        $dir = rtrim((string) config('security.signed_sheet_dir', 'storage/private/signed-sheets'), '/');
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Unable to create signed sheet directory');
        }

        $version = $this->sheets->nextVersionNo($submissionId);
        $filename = sprintf('sub_%d_v%d_%s.%s', $submissionId, $version, bin2hex(random_bytes(8)), $ext);
        $targetPath = $dir . '/' . $filename;

        $moved = false;
        if (is_uploaded_file($tmpName)) {
            $moved = move_uploaded_file($tmpName, $targetPath);
        } else {
            $moved = @rename($tmpName, $targetPath) || @copy($tmpName, $targetPath);
        }
        if (!$moved) {
            throw new RuntimeException('Could not store uploaded file');
        }

        $hash = hash_file('sha256', $targetPath);

        $db = \App\Core\Database::default();
        $db->beginTransaction();
        try {
            $before = $this->sheets->activeBySubmission($submissionId);
            $this->sheets->deactivateCurrent($submissionId);

            $this->sheets->create([
                'submission_id' => $submissionId,
                'version_no' => $version,
                'is_active' => 1,
                'uploaded_by_user_id' => (int) $user['id'],
                'uploaded_at' => now_utc(),
                'original_name' => $original,
                'mime_type' => $mime,
                'size_bytes' => (int) $file['size'],
                'storage_path' => $targetPath,
                'sha256_hash' => $hash,
                'replaced_at' => null,
            ]);

            $this->submissions->setSignedSheetStatus($submissionId, $version > 1 ? SignedSheetStatus::REPLACED : SignedSheetStatus::ATTACHED);

            $after = $this->sheets->activeBySubmission($submissionId);
            $this->audit->record(
                actorUserId: (int) $user['id'],
                entityType: 'signed_sheet',
                entityId: (int) ($after['id'] ?? 0),
                action: $version > 1 ? 'replaced' : 'uploaded',
                before: $before,
                after: $after,
                ip: $request->ip(),
                userAgent: $request->userAgent(),
            );

            $db->commit();
            return $after ?? [];
        } catch (\Throwable $e) {
            $db->rollBack();
            @unlink($targetPath);
            throw $e;
        }
    }

    public function getActive(int $submissionId, array $user): ?array
    {
        $submission = $this->submissions->findById($submissionId);
        if (!$submission) {
            throw new RuntimeException('Submission not found');
        }

        if (!(new SubmissionPolicy())->canView($user, $submission)) {
            throw new RuntimeException('Forbidden');
        }

        return $this->sheets->activeBySubmission($submissionId);
    }

    public function history(int $submissionId, array $user): array
    {
        $submission = $this->submissions->findById($submissionId);
        if (!$submission) {
            throw new RuntimeException('Submission not found');
        }

        if (!(new SubmissionPolicy())->canView($user, $submission)) {
            throw new RuntimeException('Forbidden');
        }

        return $this->sheets->history($submissionId);
    }
}
