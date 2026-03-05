<?php

declare(strict_types=1);

namespace App\Repositories;

final class UserRepository extends BaseRepository
{
    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT * FROM ats_users WHERE email = :email AND active = 1 LIMIT 1';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $sql = 'SELECT * FROM ats_users WHERE id = :id LIMIT 1';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function updateLastLogin(int $id): void
    {
        $sql = 'UPDATE ats_users SET last_login_at = :logged_at WHERE id = :id';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'logged_at' => now_utc(),
        ]);
    }
}
