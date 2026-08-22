<?php

namespace Aftermath\Logging;

use Aftermath\Tracing\TracingManager;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class AftermathLoggingHandler extends AbstractProcessingHandler
{
    public function __construct()
    {
        parent::__construct(Level::Debug);
    }

    protected function write(LogRecord $record): void
    {
        if (!app()->bound('aftermath')) {
            return;
        }

        if (config('aftermath.logging.enabled') === false) {
            return;
        }

        if ($record->level < Level::fromName(config('aftermath.logging.level', 'debug'))) {
            return;
        }

        if (config('aftermath.tracing.enabled', true)) {
            app(TracingManager::class)->startSpan($record->message, 'log');
        }

        app('aftermath')->captureLog(new \Aftermath\Event\LogEvent($record));

        if (config('aftermath.tracing.enabled', true)) {
            app(TracingManager::class)->finishCurrentSpan();
        }
    }
}