<?php

namespace Aftermath\Event;

use Illuminate\Support\Facades\Config;
use Monolog\LogRecord;

final readonly class LogEvent
{
    public function __construct(
        private readonly LogRecord $record,
    ) {}

    public function toArray(): array
    {
        return [
            'event_id' => (string) str()->uuid(),

            'type' => 'log',

            'timestamp' => date("Y-m-d H:i:s"),

            'environment' => Config::get('aftermath.environment'),

            'payload' => [
                'log' => [
                    'message' => $this->record->message,
                    'context' => $this->record->context,
                    'level' => $this->record->level->value,
                    'channel' => $this->record->channel,
                    'datetime' => $this->record->datetime,
                ],

                'runtime' => [
                    'php' => PHP_VERSION,
                ],
            ],
            ];
    }
}