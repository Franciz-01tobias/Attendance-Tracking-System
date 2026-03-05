<?php

declare(strict_types=1);

namespace App\Enums;

final class SignedSheetStatus
{
    public const MISSING = 'missing';
    public const ATTACHED = 'attached';
    public const REPLACED = 'replaced';

    public static function all(): array
    {
        return [self::MISSING, self::ATTACHED, self::REPLACED];
    }
}
