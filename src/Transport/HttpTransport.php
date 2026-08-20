<?php

namespace Aftermath\Transport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Aftermath\Transport\Transport;

class HttpTransport implements Transport
{
    public function sendEvent(array $event): void
    {
        Http::timeout(2)
            ->post($this->getUrl(Config::get('aftermath.dsn')), $event);
    }

    public function sendTrace(array $trace): void
    {
        $response = Http::timeout(2)
            ->post(sprintf('%s/trace', $this->getUrl(Config::get('aftermath.dsn'))), $trace);

        if (Config::get('aftermath_internal.debug')) {
            logger()->debug('Aftermath trace response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

    }

    public function getUrl($dsn): string
    {
        if (Config::get('aftermath_internal.debug')) {
            return "http://localhost:8081/api/ingest/{$dsn}";
        }

        return "https://ingest.aftermath.dev/api/ingest/{$dsn}";
    }
}
