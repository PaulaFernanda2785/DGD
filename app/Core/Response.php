<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    public function setStatusCode(int $code): void
    {
        http_response_code($code);
    }

    public function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    public function json(array $data, int $statusCode = 200): void
    {
        $this->setStatusCode($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function download(string $path, string $downloadName, string $mimeType = 'application/octet-stream'): void
    {
        if (!is_file($path)) {
            throw new HttpException(404, 'Arquivo nao encontrado.');
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . basename($downloadName) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}
