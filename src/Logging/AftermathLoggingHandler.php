<?php

namespace Aftermath\Logging;

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

        app('aftermath')->captureLog(new \Aftermath\Event\LogEvent($record));
    }
}