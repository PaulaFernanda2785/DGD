<?php

declare(strict_types=1);

function load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines ?: [] as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }
}

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);

    return $value === false || $value === null || $value === '' ? $default : $value;
}

function env_bool(string $key, bool $default = false): bool
{
    $value = env($key, $default);

    if (is_bool($value)) {
        return $value;
    }

    return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
}

function env_int(string $key, int $default = 0): int
{
    return (int) env($key, $default);
}

function config(string $key, mixed $default = null): mixed
{
    static $configs = [];

    $parts = explode('.', $key);
    $file = array_shift($parts);

    if (!isset($configs[$file])) {
        $path = CONFIG_PATH . DIRECTORY_SEPARATOR . $file . '.php';
        $configs[$file] = is_file($path) ? require $path : [];
    }

    $value = $configs[$file];

    foreach ($parts as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }

        $value = $value[$part];
    }

    return $value;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim((string) config('app.url', ''), '/');

    if ($base === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = rtrim(str_replace('/index.php', '', $scriptName), '/');
        $base = $scheme . '://' . $host . $basePath;
    }

    $path = '/' . ltrim($path, '/');

    return $base . ($path === '/' ? '' : $path);
}

function view_path(string $view): string
{
    return APP_PATH . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
}

function csrf_input(): string
{
    return \App\Core\Csrf::input();
}

function can(string $permission): bool
{
    return \App\Core\Permission::can($permission);
}

function flash(string $key, mixed $default = null): mixed
{
    return \App\Core\Session::consumeFlash($key, $default);
}

function old(string $key, mixed $default = ''): mixed
{
    static $old = null;

    if ($old === null) {
        $old = \App\Core\Session::consumeFlash('_old', []);
    }

    if (!is_array($old)) {
        return $default;
    }

    return $old[$key] ?? $default;
}

function status_badge(?string $text): string
{
    $text = trim((string) $text);
    $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $normalized = strtolower((string) $normalized);

    $class = 'badge-muted';

    if (str_contains($normalized, 'homologado') || str_contains($normalized, 'reconhecido') || str_contains($normalized, 'concluido') || str_contains($normalized, 'aprovado')) {
        $class = 'badge-success';
    }

    if (str_contains($normalized, 'pendente') || str_contains($normalized, 'aguardando') || str_contains($normalized, 'preparacao') || str_contains($normalized, 'prazo')) {
        $class = 'badge-warning';
    }

    if (str_contains($normalized, 'nao homologado') || str_contains($normalized, 'nao reconhecido') || str_contains($normalized, 'indeferido')) {
        $class = 'badge-danger';
    }

    if (str_contains($normalized, 'enviado') || str_contains($normalized, 'analise') || str_contains($normalized, 'solicitado')) {
        $class = 'badge-info';
    }

    return '<span class="status-badge ' . e($class) . '">' . e($text ?: '-') . '</span>';
}
