<?php

namespace Aftermath\Event;

use Illuminate\Support\Facades\Config;
use Aftermath\Tracing\TracingManager;

abstract readonly class AbstractEvent
{
    final public function toArray(): array
    {
        return [
            'event_id' => (string) str()->uuid(),

            'trace_id' => app(TracingManager::class)->getCurrentTrace()?->getId(),

            'type' => $this->type(),

            'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),

            'environment' => Config::get('aftermath.environment'),

            'payload' => $this->payload(),
        ];
    }

    abstract protected function type(): string;

    abstract protected function payload(): array;

    protected function runtimeContext(): array
    {
        return [
            'name' => 'PHP',
            'version' => PHP_VERSION,
        ];
    }
}
