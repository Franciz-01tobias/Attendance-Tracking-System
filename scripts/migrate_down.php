<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/autoload.php';
require_once __DIR__ . '/../bootstrap/helpers.php';

use App\Core\Database;

$pdo = Database::default();
$files = glob(__DIR__ . '/../database/migrations/*_down.sql') ?: [];
rsort($files);

foreach ($files as $file) {
    $name = basename($file);
    echo "Rolling back {$name} ...\n";
    $sql = file_get_contents($file);
    if ($sql === false) {
        throw new RuntimeException('Cannot read migration rollback: ' . $name);
    }

    $pdo->exec($sql);
}

echo "Rollback complete.\n";
