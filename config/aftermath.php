<?php

return [
    'dsn' => env('AFTERMATH_DSN'),

    'environment' => env(
        'AFTERMATH_ENVIRONMENT',
        env('APP_ENV', 'production')
    ),

    'enabled' => env('AFTERMATH_ENABLED', true),
];