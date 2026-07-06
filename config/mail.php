<?php

declare(strict_types=1);

return [
    'mailer' => env('MAIL_MAILER', 'log'),
    'smtp' => [
        'host' => env('MAIL_HOST', ''),
        'port' => env_int('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME', ''),
        'password' => env('MAIL_PASSWORD', ''),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'auth' => env_bool('MAIL_SMTP_AUTH', true),
        'timeout' => env_int('MAIL_TIMEOUT', 15),
        'ca_file' => env('MAIL_CA_FILE', ''),
        'verify_peer' => env_bool('MAIL_VERIFY_PEER', true),
    ],
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', ''),
        'name' => env('MAIL_FROM_NAME', 'DGD - CEDEC-PA'),
    ],
];
