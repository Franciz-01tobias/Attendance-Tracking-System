<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $body,
        public readonly array $files,
        public readonly array $server,
        public readonly array $headers,
    ) {
    }

    public static function capture(): self
    {
        $headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];
        $method = strtoupper($_POST['_method'] ?? $_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        return new self(
            method: $method,
            path: rtrim($path, '/') ?: '/',
            query: $_GET,
            body: $_POST,
            files: $_FILES,
            server: $_SERVER,
            headers: array_change_key_case($headers, CASE_LOWER),
        );
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function expectsJson(): bool
    {
        $accept = strtolower($this->headers['accept'] ?? '');
        return str_contains($accept, 'application/json') || str_starts_with($this->path, '/api/');
    }

    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? 'unknown';
    }
}
