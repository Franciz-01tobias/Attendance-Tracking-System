<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<int, array{pattern: string, handler: callable|array, middleware: array<int, callable>}>> */
    private array $routes = [];

    public function add(string $method, string $path, callable|array $handler, array $middleware = []): void
    {
        $method = strtoupper($method);
        $this->routes[$method][] = [
            'pattern' => rtrim($path, '/') ?: '/',
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): never
    {
        $methodRoutes = $this->routes[$request->method] ?? [];

        foreach ($methodRoutes as $route) {
            $params = $this->match($route['pattern'], $request->path);
            if ($params === null) {
                continue;
            }

            foreach ($route['middleware'] as $mw) {
                $mw($request, $params);
            }

            $handler = $route['handler'];
            if (is_array($handler)) {
                [$class, $method] = $handler;
                $instance = new $class();
                $instance->{$method}($request, $params);
                exit;
            }

            $handler($request, $params);
            exit;
        }

        Response::json(['ok' => false, 'message' => 'Route not found'], 404);
    }

    private function match(string $pattern, string $path): ?array
    {
        $patternParts = explode('/', trim($pattern, '/'));
        $pathParts = explode('/', trim($path, '/'));

        if ($pattern === '/' && $path === '/') {
            return [];
        }

        if (count($patternParts) !== count($pathParts)) {
            return null;
        }

        $params = [];
        foreach ($patternParts as $i => $part) {
            $value = $pathParts[$i] ?? '';
            if (preg_match('/^\{([a-zA-Z0-9_]+)\}$/', $part, $m)) {
                $params[$m[1]] = $value;
                continue;
            }

            if ($part !== $value) {
                return null;
            }
        }

        return $params;
    }
}
