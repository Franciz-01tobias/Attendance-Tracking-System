<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

final class AuthController
{
    public function loginPage(Request $request, array $params): void
    {
        if (Auth::check()) {
            $role = $_SESSION['user_role'] ?? 'cr';
            Response::redirect('/dashboard/' . $role);
        }

        Response::view('auth/login', [
            'title' => 'Sign In',
            'csrf' => Csrf::token(),
            'error' => $_SESSION['flash_error'] ?? null,
        ]);
    }

    public function login(Request $request, array $params): void
    {
        try {
            $email = (string) $request->input('email', '');
            $password = (string) $request->input('password', '');
            $user = (new AuthService())->authenticate($email, $password);

            if ($request->expectsJson()) {
                Response::json(['ok' => true, 'user' => ['id' => $user['id'], 'role' => $user['role']]]);
            }

            Response::redirect('/dashboard/' . $user['role']);
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                Response::json(['ok' => false, 'message' => $e->getMessage()], 422);
            }

            $_SESSION['flash_error'] = $e->getMessage();
            Response::redirect('/login');
        }
    }

    public function logout(Request $request, array $params): void
    {
        Auth::logout();

        if ($request->expectsJson()) {
            Response::json(['ok' => true]);
        }

        Response::redirect('/login');
    }
}
