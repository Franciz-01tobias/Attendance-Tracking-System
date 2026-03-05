<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

final class ReadOnlyPdo
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        $this->assertReadOnly($query);
        if ($fetchMode === null) {
            return $this->pdo->query($query);
        }

        return $this->pdo->query($query, $fetchMode, ...$fetchModeArgs);
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        $this->assertReadOnly($query);
        return $this->pdo->prepare($query, $options);
    }

    public function exec(string $statement): int|false
    {
        $this->assertReadOnly($statement);
        return $this->pdo->exec($statement);
    }

    public function beginTransaction(): bool
    {
        throw new \RuntimeException('Transactions are not allowed on read-only Marazone connection');
    }

    public function getWrappedPdo(): PDO
    {
        return $this->pdo;
    }

    private function assertReadOnly(string $sql): void
    {
        $trim = ltrim($sql);
        $verb = strtoupper((string) strtok($trim, " \t\n\r"));
        $blocked = ['INSERT', 'UPDATE', 'DELETE', 'REPLACE', 'ALTER', 'DROP', 'CREATE', 'TRUNCATE', 'GRANT', 'REVOKE', 'LOCK', 'UNLOCK', 'CALL'];

        if (in_array($verb, $blocked, true)) {
            throw new \RuntimeException('Write query blocked on Marazone read-only connection: ' . $verb);
        }
    }
}
