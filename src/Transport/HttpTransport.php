<?php

namespace Aftermath\Transport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class HttpTransport
{
    public function send(array $event): void
    {
        Http::timeout(2)
            ->post($this->getUrl(Config::get('aftermath.dsn')), $event);
    }

    public function sendTrace(array $trace): void
    {
        Http::timeout(2)
            ->post(sprintf('%s/trace', $this->getUrl(Config::get('aftermath.dsn'))), $trace);
    }

    public function getUrl($dsn): string
    {
        if (Config::get('aftermath_internal.debug')) {
            return "http://localhost:8081/api/ingest/{$dsn}";
        }

        return "https://ingest.aftermath.dev/api/ingest/{$dsn}";
    }
}
