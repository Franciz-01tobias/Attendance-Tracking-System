<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

final class Database
{
    private static ?PDO $default = null;
    private static ?ReadOnlyPdo $marazone = null;

    public static function default(): PDO
    {
        if (self::$default instanceof PDO) {
            return self::$default;
        }

        $cfg = config('database.default');
        self::$default = self::connect($cfg);
        return self::$default;
    }

    public static function marazoneReadOnly(): ReadOnlyPdo
    {
        if (self::$marazone instanceof ReadOnlyPdo) {
            return self::$marazone;
        }

        $cfg = config('database.marazone_read');
        self::$marazone = new ReadOnlyPdo(self::connect($cfg));
        return self::$marazone;
    }

    private static function connect(array $cfg): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['port'],
            $cfg['database'],
            $cfg['charset'] ?? 'utf8mb4'
        );

        try {
            $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (\PDOException $e) {
            $drivers = implode(', ', PDO::getAvailableDrivers());
            throw new RuntimeException(
                'Database connection failed: ' . $e->getMessage() . '. Available PDO drivers: [' . $drivers . ']',
                0,
                $e
            );
        }

        if (!empty($cfg['collation'])) {
            $pdo->exec("SET NAMES {$cfg['charset']} COLLATE {$cfg['collation']}");
        }

        return $pdo;
    }
}
