<?php

declare(strict_types=1);

namespace App\Policies;

use App\Repositories\SubmissionRepository;

final class SubmissionPolicy
{
    public function canView(array $user, array $submission): bool
    {
        if ($user['role'] === 'admin') {
            return true;
        }

        if ($user['role'] === 'lecturer' && (int) $submission['lecturer_user_id'] === (int) $user['id']) {
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
            && (int) $submission['lecturer_user_id'] === (int) $user['id']
            && $submission['status'] === 'pending';
    }

    public function canUploadSheet(array $user, array $submission): bool
    {
        return $this->canEditItems($user, $submission);
    }

    public function canOverride(array $user): bool
    {
        return $user['role'] === 'admin';
    }
}
