<?php

namespace Aftermath\Transport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Aftermath\Transport\Transport;

class HttpTransport extends Transport
{
    public function sendEvents(array $events): void
    {
        Http::timeout(2)
            ->post(
                $this->getUrl(Config::get('aftermath.dsn')), 
                ['events' => $events]
            );
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
}
