<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\DecisionController;
use App\Controllers\HealthController;
use App\Controllers\ReportController;
use App\Controllers\SessionController;
use App\Controllers\SignedSheetController;
use App\Controllers\SubmissionController;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;

$requireAuth = static function (Request $request, array $params): void {
    if (!Auth::check()) {
        if ($request->expectsJson()) {
            Response::json(['ok' => false, 'message' => 'Unauthenticated'], 401);
        }
        Response::redirect('/login');
    }
};

$requireCsrf = static function (Request $request, array $params): void {
    Csrf::ensureFor($request);
};

$router->add('GET', '/', [HealthController::class, 'index']);
$router->add('GET', '/login', [AuthController::class, 'loginPage']);
$router->add('POST', '/auth/login', [AuthController::class, 'login'], [$requireCsrf]);
$router->add('POST', '/auth/logout', [AuthController::class, 'logout'], [$requireAuth, $requireCsrf]);

$router->add('GET', '/dashboard', static function (Request $request, array $params): void {
    if (!Auth::check()) {
        Response::redirect('/login');
    }
    Response::redirect('/dashboard/' . ($_SESSION['user_role'] ?? 'cr'));
}, [$requireAuth]);
$router->add('GET', '/dashboard/{role}', [DashboardController::class, 'show'], [$requireAuth]);
$router->add('GET', '/sessions/{id}', [SessionController::class, 'show'], [$requireAuth]);

$router->add('POST', '/sessions/{id}/submissions', [SubmissionController::class, 'create'], [$requireAuth, $requireCsrf]);
$router->add('PATCH', '/submissions/{id}/items/{itemId}', [SubmissionController::class, 'updateItem'], [$requireAuth, $requireCsrf]);

$router->add('POST', '/submissions/{id}/signed-sheet', [SignedSheetController::class, 'upload'], [$requireAuth, $requireCsrf]);
$router->add('GET', '/submissions/{id}/signed-sheet', [SignedSheetController::class, 'download'], [$requireAuth]);
$router->add('GET', '/submissions/{id}/signed-sheet/history', [SignedSheetController::class, 'history'], [$requireAuth]);

$router->add('POST', '/submissions/{id}/approve', [DecisionController::class, 'approve'], [$requireAuth, $requireCsrf]);
$router->add('POST', '/submissions/{id}/reject', [DecisionController::class, 'reject'], [$requireAuth, $requireCsrf]);
$router->add('POST', '/submissions/{id}/override', [AdminController::class, 'override'], [$requireAuth, $requireCsrf]);

$router->add('GET', '/reports/attendance', [ReportController::class, 'attendance'], [$requireAuth]);
$router->add('GET', '/reports/approval-turnaround', [ReportController::class, 'turnaround'], [$requireAuth]);
$router->add('GET', '/reports/escalations', [ReportController::class, 'escalations'], [$requireAuth]);
