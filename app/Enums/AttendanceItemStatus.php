<?php

declare(strict_types=1);

namespace App\Enums;

final class AttendanceItemStatus
{
    public const PRESENT = 'present';
    public const ABSENT = 'absent';
    public const LATE = 'late';

    public static function all(): array
    {
        return [self::PRESENT, self::ABSENT, self::LATE];
    }
}
