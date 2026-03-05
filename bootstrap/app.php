<?php

declare(strict_types=1);

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/helpers.php';

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Logger;

if (class_exists(\Dotenv\Dotenv::class) && file_exists(__DIR__ . '/../.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

error_reporting(E_ALL);
ini_set('display_errors', config('app.debug') ? '1' : '0');

date_default_timezone_set((string) config('app.timezone', 'UTC'));

set_exception_handler(static function (Throwable $e): void {
    Logger::error('Unhandled exception', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);

    if ((bool) config('app.debug', false)) {
        Response::json([
            'ok' => false,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500);
    }

    Response::json(['ok' => false, 'message' => 'Internal server error'], 500);
});

$sessionName = (string) config('security.session_name', 'attendance_session');
if (session_status() === PHP_SESSION_NONE) {
    session_name($sessionName);
    session_set_cookie_params([
        'lifetime' => (int) config('security.session_lifetime', 7200),
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$router = new Router();
require __DIR__ . '/../routes/web.php';

$request = Request::capture();
$router->dispatch($request);
