<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = rtrim(str_replace('/index.php', '', $scriptName), '/');

        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath)) ?: '/';
        }

        $path = '/' . trim($uri, '/');

        return $path === '//' ? '/' : $path;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        return $this->from($_GET, $key, $default);
    }

    public function post(?string $key = null, mixed $default = null): mixed
    {
        return $this->from($_POST, $key, $default);
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        return $this->from(array_merge($_GET, $_POST), $key, $default);
    }

    public function files(?string $key = null): mixed
    {
        return $key === null ? $_FILES : ($_FILES[$key] ?? null);
    }

    public function ip(): ?string
    {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    public function userAgent(): ?string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? null;
    }

    public function expectsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

        return str_contains($accept, 'application/json') || strtolower($requestedWith) === 'xmlhttprequest';
    }

    private function from(array $source, ?string $key, mixed $default): mixed
    {
        if ($key === null) {
            return $source;
        }

        return $source[$key] ?? $default;
    }
}
