<?php

declare(strict_types=1);

namespace App\Enums;

final class SubmissionStatus
{
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const OVERRIDDEN = 'overridden';

    public static function all(): array
    {
        return [self::PENDING, self::APPROVED, self::REJECTED, self::OVERRIDDEN];
    }
}
