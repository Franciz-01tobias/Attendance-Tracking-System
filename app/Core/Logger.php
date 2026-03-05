<?php

declare(strict_types=1);

namespace App\Core;

final class Logger
{
    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    private static function write(string $level, string $message, array $context): void
    {
        $line = sprintf('[%s] %s %s %s', now_utc(), $level, $message, json_encode($context));
        $logFile = __DIR__ . '/../../storage/logs/app.log';

        if (class_exists('Monolog\\Logger') && class_exists('Monolog\\Handler\\StreamHandler')) {
            /** @var class-string<\Monolog\Logger> $loggerClass */
            $loggerClass = 'Monolog\\Logger';
            /** @var class-string<\Monolog\Handler\StreamHandler> $handlerClass */
            $handlerClass = 'Monolog\\Handler\\StreamHandler';
            $logger = new $loggerClass('attendance');
            $logger->pushHandler(new $handlerClass($logFile));
            $logger->log(strtolower($level), $message, $context);
            return;
        }

        error_log($line . PHP_EOL, 3, $logFile);
    }
}
