<?php

declare(strict_types=1);

return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env_int('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'dgd_db'),
            'username' => env('DB_USERNAME', 'dgd_app'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
        'cadastro_emergencial' => [
            'driver' => 'mysql',
            'host' => env('CADASTRO_EMERGENCIAL_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env_int('CADASTRO_EMERGENCIAL_DB_PORT', env_int('DB_PORT', 3306)),
            'database' => env('CADASTRO_EMERGENCIAL_DB_DATABASE', 'cadastro_emergencial'),
            'username' => env('CADASTRO_EMERGENCIAL_DB_USERNAME', env('DB_USERNAME', '')),
            'password' => env('CADASTRO_EMERGENCIAL_DB_PASSWORD', env('DB_PASSWORD', '')),
            'charset' => env('CADASTRO_EMERGENCIAL_DB_CHARSET', 'utf8mb4'),
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false],
        ],
    ],
];
