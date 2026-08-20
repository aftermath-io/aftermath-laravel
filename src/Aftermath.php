<?php

namespace Aftermath;

use Aftermath\Transport\Transport;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Configuration\Exceptions;
use Aftermath\Event\ExceptionEvent;
use Aftermath\Event\LogEvent;
use Throwable;

final class Aftermath
{
    protected static string $traceId;

    public function __construct(
        private readonly Transport $transport,
        private bool $exceptionWasReported = false,
    )
    {}

    public static function handles(Exceptions $exceptions)
    {
        $exceptions->reportable(function (Throwable $throwable) {
            app(Aftermath::class)->captureException($throwable);
        });
    }

    private static function enabled(): bool
    {
        return Config::get('aftermath.enabled', true);
    } 

    private function captureException(Throwable $throwable): void
    {
        if (!self::enabled()) {
            return;
        }

        try {
            $event = new ExceptionEvent($throwable);
    
            $this->transport->sendEvent($event->toArray());

            $this->exceptionWasReported = true;
        } catch (\Throwable $e) {
            if (Config::get('aftermath_internal.debug')) {
                throw $e;
            }
        }
    }

    public function captureLog(LogEvent $event): void
    {
        if (!self::enabled()) {
            return;
        }

        try {
            $this->transport->sendEvent($event->toArray());
        } catch (\Throwable $e) {
            if (Config::get('aftermath_internal.debug')) {
                throw $e;
            }
        }
    }

    public function exceptionWasReported(): bool
    {
        return $this->exceptionWasReported;
    }

    public function resetExceptionReported(): void
    {
        $this->exceptionWasReported = false;
    }
}