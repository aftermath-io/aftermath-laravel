<?php

return [
    'dsn' => env('AFTERMATH_DSN'),

    'environment' => env(
        'AFTERMATH_ENVIRONMENT',
        env('APP_ENV', 'production')
    ),

    'enabled' => env('AFTERMATH_ENABLED', true),

    'logging' => [
        'enabled' => env('AFTERMATH_LOGGING_ENABLED', true),
        'level' => env('AFTERMATH_LOGGING_LEVEL', env('LOG_LEVEL', 'debug')),
    ],
];