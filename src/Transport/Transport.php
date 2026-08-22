<?php

namespace Aftermath\Transport;

abstract class Transport
{
    abstract public function sendEvents(array $events): void;
    abstract public function sendTrace(array $trace): void;

    public function getUrl($dsn): string
    {
        if (config('aftermath_internal.debug')) {
            return "http://localhost:8081/api/ingest/{$dsn}";
        }

        return "https://ingest.aftermath.dev/api/ingest/{$dsn}";
    }
}