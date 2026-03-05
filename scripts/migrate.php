<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/autoload.php';
require_once __DIR__ . '/../bootstrap/helpers.php';

use App\Core\Database;

$pdo = Database::default();
$pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (id INT AUTO_INCREMENT PRIMARY KEY, migration_name VARCHAR(255) NOT NULL UNIQUE, applied_at DATETIME NOT NULL)');

$files = glob(__DIR__ . '/../database/migrations/*_up.sql') ?: [];
sort($files);

$stmt = $pdo->query('SELECT migration_name FROM schema_migrations');
$applied = $stmt ? array_column($stmt->fetchAll(), 'migration_name') : [];

foreach ($files as $file) {
    $name = basename($file);
    if (in_array($name, $applied, true)) {
        continue;
    }

    echo "Applying {$name} ...\n";
    $sql = file_get_contents($file);
    if ($sql === false) {
        throw new RuntimeException('Cannot read migration: ' . $name);
    }

    $pdo->beginTransaction();
    try {
        $pdo->exec($sql);
        $ins = $pdo->prepare('INSERT INTO schema_migrations (migration_name, applied_at) VALUES (:name, :at)');
        $ins->execute(['name' => $name, 'at' => now_utc()]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

echo "Migration complete.\n";
