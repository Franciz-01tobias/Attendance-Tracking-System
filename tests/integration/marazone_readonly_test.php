<?php

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap/autoload.php';

use App\Core\ReadOnlyPdo;

if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
    echo "marazone_readonly_test skipped (pdo_sqlite not installed)\n";
    exit(0);
}

$pdo = new PDO('sqlite::memory:');
$readOnly = new ReadOnlyPdo($pdo);

$passed = false;
try {
    $readOnly->exec('INSERT INTO users(name) VALUES (\'x\')');
} catch (RuntimeException $e) {
    $passed = str_contains($e->getMessage(), 'Write query blocked');
}

if (!$passed) {
    throw new RuntimeException('marazone_readonly_test failed');
}

echo "marazone_readonly_test passed\n";
