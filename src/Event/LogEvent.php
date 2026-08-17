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

            'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),

            'environment' => Config::get('aftermath.environment'),

            'payload' => [
                'level' => $this->record->level->toPsrLogLevel(),

                'log' => [
                    'message' => $this->record->message,
                    'formatted_message' => is_string($this->record->formatted)
                        ? $this->record->formatted
                        : null,
                    'parameters' => [],
                    'logger' => $this->record->channel,
                ],

                'metadata' => [
                    'contexts' => array_filter([
                        'runtime' => [
                            'name' => 'PHP',
                            'version' => PHP_VERSION,
                        ],
                        'log' => $this->record->context ?: null,
                    ]),
                    'extra' => $this->record->extra ?: null,
                ],
            ],
        ];
    }
}