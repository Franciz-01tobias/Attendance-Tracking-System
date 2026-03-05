<?php

declare(strict_types=1);

function env_value(string $key, ?string $default = null): ?string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $envPath = __DIR__ . '/../.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $line, 2);
                $cache[trim($k)] = trim($v);
            }
        }
    }

    return $_ENV[$key] ?? $_SERVER[$key] ?? $cache[$key] ?? $default;
}

function config(string $path, mixed $default = null): mixed
{
    static $store = [];
    [$file, $key] = array_pad(explode('.', $path, 2), 2, null);
    if (!isset($store[$file])) {
        $fullPath = __DIR__ . '/../config/' . $file . '.php';
        $store[$file] = file_exists($fullPath) ? require $fullPath : [];
    }

    if ($key === null) {
        return $store[$file] ?? $default;
    }

    return $store[$file][$key] ?? $default;
}

function now_utc(): string
{
    return (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
}

function app_timezone(): DateTimeZone
{
    return new DateTimeZone(env_value('APP_TIMEZONE', 'UTC') ?? 'UTC');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
