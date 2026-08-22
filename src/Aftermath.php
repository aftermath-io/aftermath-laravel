<?php

namespace Aftermath;

use Aftermath\Tracing\TracingManager;
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
        private readonly AftermathEventBuffer $eventBuffer,
        private bool $exceptionWasReported = false,
    )
    {}

    public static function handles(Exceptions $exceptions)
    {
        $exceptions->reportable(function (Throwable $throwable) {
            if (config('aftermath.tracing.enabled', true)) {
                app(TracingManager::class)->startSpan($throwable->getMessage(), 'exception');
            }
            $exceptionEvent = new ExceptionEvent($throwable);
            app(self::class)->captureException($exceptionEvent);
            if (config('aftermath.tracing.enabled', true)) {
                app(TracingManager::class)->finishCurrentSpan();
            }
        });
    }

    private static function enabled(): bool
    {
        return Config::get('aftermath.enabled', true);
    } 

    private function captureException(ExceptionEvent $event): self
    {
        if (!self::enabled()) {
            return $this;
        }

        $this->eventBuffer->push($event);
        $this->exceptionWasReported = true;

        return $this;
    }

    public function captureLog(LogEvent $event): self
    {
        if (!self::enabled()) {
            return $this;
        }

        $this->eventBuffer->push($event);

        return $this;
    }

    public function exceptionWasReported(): bool
    {
        return $this->exceptionWasReported;
    }

    public function resetExceptionReported(): void
    {
        $this->exceptionWasReported = false;
    }

    public function flushEventBuffer(): void
    {
        if (!self::enabled()) {
            return;
        }

        if ($this->eventBuffer->isEmpty()) {
            return;
        }

        $events = $this->eventBuffer->pull();

        try {
            $this->transport->sendEvents(array_map(fn($event) => $event->toArray(), $events));
        } catch (\Throwable $e) {
            if (Config::get('aftermath_internal.debug')) {
                throw $e;
            }
        }
    }
}