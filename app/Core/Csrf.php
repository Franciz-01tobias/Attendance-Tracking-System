<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION[self::KEY];
    }

    public static function validate(?string $token): bool
    {
        return is_string($token) && hash_equals((string) ($_SESSION[self::KEY] ?? ''), $token);
    }

    public static function ensureFor(Request $request): void
    {
        if (in_array($request->method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }

        $token = $request->input('_csrf')
            ?? ($request->headers['x-csrf-token'] ?? null);

        if (!self::validate(is_string($token) ? $token : null)) {
            Response::json([
                'ok' => false,
                'message' => 'Invalid CSRF token',
            ], 419);
        }
    }
}
