<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], string $layout = 'app'): void
    {
        $viewFile = view_path($view);

        if (!is_file($viewFile)) {
            throw new HttpException(500, 'View nao encontrada: ' . $view);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = view_path('layouts/' . $layout);

        if (is_file($layoutFile)) {
            require $layoutFile;
            return;
        }

        echo $content;
    }

    public static function renderError(int $statusCode, string $message): void
    {
        $view = 'errors/' . $statusCode;
        $viewFile = view_path($view);

        if (is_file($viewFile)) {
            self::render($view, ['message' => $message, 'statusCode' => $statusCode], 'error');
            return;
        }

        echo '<h1>' . e($statusCode) . '</h1>';
        echo '<p>' . e($message) . '</p>';
    }
}
