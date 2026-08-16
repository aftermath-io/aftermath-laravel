<?php

return [
    'channels' => [
        'aftermath' => [
            'driver' => 'monolog',
            'handler' => \Aftermath\Logging\AftermathLoggingHandler::class,
            'level' => 'debug',
        ],
    ],
];
