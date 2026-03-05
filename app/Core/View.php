<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $template, array $data = []): string
    {
        $layoutPath = __DIR__ . '/../Views/layout.php';
        $viewPath = __DIR__ . '/../Views/' . $template . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException('View not found: ' . $template);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if (file_exists($layoutPath)) {
            ob_start();
            require $layoutPath;
            return (string) ob_get_clean();
        }

        return (string) $content;
    }
}
