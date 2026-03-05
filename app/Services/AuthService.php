<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Repositories\UserRepository;
use RuntimeException;

final class AuthService
{
    public function __construct(private readonly UserRepository $ats_users = new UserRepository())
    {
    }

    public function authenticate(string $email, string $password): array
    {
        $this->throttle($email);

        $user = $this->ats_users->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['login_attempts'][$email] = ($_SESSION['login_attempts'][$email] ?? 0) + 1;
            throw new RuntimeException('Invalid credentials');
        }

        $_SESSION['login_attempts'][$email] = 0;
        Auth::login($user);
        $this->ats_users->updateLastLogin((int) $user['id']);

        return $user;
    }

    private function throttle(string $email): void
    {
        $attempts = (int) ($_SESSION['login_attempts'][$email] ?? 0);
        if ($attempts >= 5) {
            throw new RuntimeException('Too many login attempts. Try again later.');
        }
    }
}
