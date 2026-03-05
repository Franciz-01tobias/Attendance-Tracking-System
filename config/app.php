<?php

declare(strict_types=1);

return [
    'name' => env_value('APP_NAME', 'Attendance Professional System'),
    'env' => env_value('APP_ENV', 'local'),
    'debug' => filter_var(env_value('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),
    'timezone' => env_value('APP_TIMEZONE', 'UTC'),
    'url' => env_value('APP_URL', 'http://localhost'),
];
