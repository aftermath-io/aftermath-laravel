<?php

namespace Aftermath\Event;

use Monolog\LogRecord;

final readonly class LogEvent extends AbstractEvent
{
    public function __construct(
        private readonly LogRecord $record,
    ) {}

    protected function type(): string
    {
        return 'log';
    }

    protected function payload(): array
    {
        return [
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
                    'runtime' => $this->runtimeContext(),
                    'log' => $this->record->context ?: null,
                ]),
                'extra' => $this->record->extra ?: null,
            ],
        ];
    }
}