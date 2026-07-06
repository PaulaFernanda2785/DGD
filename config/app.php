<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'DGD'),
    'env' => env('APP_ENV', 'local'),
    'debug' => env_bool('APP_DEBUG', false),
    'url' => rtrim((string) env('APP_URL', ''), '/'),
    'timezone' => env('APP_TIMEZONE', 'America/Belem'),
    'session' => [
        'name' => env('SESSION_NAME', 'DGDSESSID'),
        'lifetime' => env_int('SESSION_LIFETIME', 7200),
        'secure' => env_bool('SESSION_SECURE', false),
        'same_site' => 'Lax',
    ],
];
