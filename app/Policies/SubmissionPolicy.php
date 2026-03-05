<?php

declare(strict_types=1);

namespace App\Policies;

final class SubmissionPolicy
{
    public function canView(array $user, array $submission): bool
    {
        if ($user['role'] === 'admin') {
            return true;
        }

        if ($user['role'] === 'lecturer' && $this->isSubmissionLecturer($user, $submission)) {
            return true;
        }

        if ($user['role'] === 'cr' && (int) $submission['cr_user_id'] === (int) $user['id']) {
            return true;
        }

        return false;
    }

    public function canEditItems(array $user, array $submission): bool
    {
        return $user['role'] === 'lecturer'
            && $submission['status'] === 'pending'
            && $this->isSubmissionLecturer($user, $submission);
    }

    public function canUploadSheet(array $user, array $submission): bool
    {
        return $this->canEditItems($user, $submission);
    }

    public function canOverride(array $user): bool
    {
        return $user['role'] === 'admin';
    }

    private function isSubmissionLecturer(array $user, array $submission): bool
    {
        $localMatch = (int) ($submission['lecturer_user_id'] ?? 0) > 0
            && (int) ($submission['lecturer_user_id'] ?? 0) === (int) $user['id'];

        $marazoneMatch = ($user['marazone_user_id'] ?? null) !== null
            && ($submission['lecturer_marazone_user_id'] ?? null) !== null
            && (string) $user['marazone_user_id'] === (string) $submission['lecturer_marazone_user_id'];

        return $localMatch || $marazoneMatch;
    }
}
