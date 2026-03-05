<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\SessionRepository;

final class DashboardController
{
    public function show(Request $request, array $params): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::redirect('/login');
        }

        $role = $params['role'] ?? $user['role'];
        if ($role !== $user['role'] && $user['role'] !== 'admin') {
            Response::json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        $sessions = (new SessionRepository())->listForRole($user);
        $view = match ($role) {
            'admin' => 'dashboard/admin',
            'lecturer' => 'dashboard/lecturer',
            default => 'dashboard/cr',
        };

        Response::view($view, [
            'title' => ucfirst($role) . ' Dashboard',
            'user' => $user,
            'sessions' => $sessions,
            'csrf' => Csrf::token(),
        ]);
    }
}
