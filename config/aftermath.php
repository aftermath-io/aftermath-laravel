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

    'tracing' => [
        'enabled' => env('AFTERMATH_TRACING_ENABLED', true),
        'sample_rate' => env('AFTERMATH_TRACING_SAMPLE_RATE', 1.0),
        'manager_class' => Aftermath\Tracing\AftermathTracingManager::class,
    ],
];