<?php

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap/autoload.php';
require_once __DIR__ . '/../../bootstrap/helpers.php';

use App\Sync\PdoMarazoneReader;

if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
    echo "marazone_contract_test skipped (pdo_mysql not installed)\n";
    exit(0);
}

try {
    $contract = (new PdoMarazoneReader())->validateContract();
    if (!$contract['ok']) {
        echo "marazone_contract_test failed: missing tables: " . implode(', ', $contract['missing_tables']) . "\n";
        exit(1);
    }

    echo "marazone_contract_test passed\n";
} catch (Throwable $e) {
    echo "marazone_contract_test skipped/failed: " . $e->getMessage() . "\n";
    exit(1);
}
