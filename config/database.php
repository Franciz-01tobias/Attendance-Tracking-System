<?php

declare(strict_types=1);

return [
    'default' => [
        'driver' => 'mysql',
        'host' => env_value('DB_HOST', '127.0.0.1'),
        'port' => (int) env_value('DB_PORT', '3306'),
        'database' => env_value('DB_DATABASE', 'marazone_sms'),
        'username' => env_value('DB_USERNAME', 'sms'),
        'password' => env_value('DB_PASSWORD', 'sms@2026'),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
    'marazone_read' => [
        'driver' => 'mysql',
        'host' => env_value('MARAZONE_DB_HOST', '127.0.0.1'),
        'port' => (int) env_value('MARAZONE_DB_PORT', '3306'),
        'database' => env_value('MARAZONE_DB_DATABASE', 'marazone_sms'),
        'username' => env_value('MARAZONE_DB_USERNAME', 'sms'),
        'password' => env_value('MARAZONE_DB_PASSWORD', 'sms@2026'),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
];
