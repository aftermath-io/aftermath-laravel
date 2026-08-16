<?php

namespace Aftermath;

use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Configuration\Exceptions;
use Aftermath\Event\ExceptionEvent;
use Aftermath\Event\LogEvent;
use Aftermath\Transport\HttpTransport;
use Throwable;

final class Aftermath
{
    public function __construct(
        private readonly HttpTransport $transport,
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
    
            $this->transport->send($event->toArray());
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
            $this->transport->send($event->toArray());
        } catch (\Throwable $e) {
            if (Config::get('aftermath_internal.debug')) {
                throw $e;
            }
        }
    }
}