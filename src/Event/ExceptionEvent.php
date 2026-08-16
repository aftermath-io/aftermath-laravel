<?php

namespace Aftermath\Event;

use Illuminate\Support\Facades\Config;

final readonly class ExceptionEvent
{
    public function __construct(
        public readonly \Throwable $throwable,
    ) {}

    public function toArray(): array
    {
        return [
            'event_id' => (string) str()->uuid(),

            'type' => 'exception',

            'timestamp' => date("Y-m-d H:i:s"),

            'environment' => Config::get('aftermath.environment'),

            'payload' => [
                'exception' => [
                    'class' => $this->throwable::class,
                    'message' => $this->throwable->getMessage(),
                    'file' => $this->throwable->getFile(),
                    'line' => $this->throwable->getLine(),
                    'trace' => $this->throwable->getTrace(),
                ],

                'runtime' => [
                    'php' => PHP_VERSION,
                ],
            ],
        ];
    }
}