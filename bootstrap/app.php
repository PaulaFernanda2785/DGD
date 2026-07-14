<?php

declare(strict_types=1);

use App\Core\App;

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'app');
define('CONFIG_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'config');
define('STORAGE_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'storage');

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = APP_PATH . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

require APP_PATH . DIRECTORY_SEPARATOR . 'Helpers' . DIRECTORY_SEPARATOR . 'functions.php';

load_env(BASE_PATH . DIRECTORY_SEPARATOR . '.env');

$configuredPublicPath = trim((string) env('PUBLIC_PATH', ''));
$frontController = (string) ($_SERVER['SCRIPT_FILENAME'] ?? '');
$frontControllerPath = $frontController !== '' ? realpath($frontController) : false;
$detectedPublicPath = $frontControllerPath !== false && basename($frontControllerPath) === 'index.php'
    ? dirname($frontControllerPath)
    : '';

define(
    'PUBLIC_PATH',
    $configuredPublicPath !== ''
        ? rtrim($configuredPublicPath, '/\\')
        : ($detectedPublicPath !== '' ? $detectedPublicPath : BASE_PATH . DIRECTORY_SEPARATOR . 'public')
);

$appConfig = require CONFIG_PATH . DIRECTORY_SEPARATOR . 'app.php';

date_default_timezone_set($appConfig['timezone']);

return new App($appConfig);
