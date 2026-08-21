<?php

namespace Aftermath\Instrumentation;

use Aftermath\Tracing\TracingManager;
use Illuminate\Support\Facades\Http;

class HttpInstrumentation
{
    public function __construct(
        protected readonly TracingManager $tracingManager,
    )
    {
    }

    public function boot()
    {
        Http::globalMiddleware(self::middleware(...));
    }

    public function middleware($request, $next)
    {
        $span = $this->tracingManager->startSpan(
            name: $request->method() . ' ' . $request->url(),
            kind: 'http',
            parentSpanId: $this->tracingManager->getCurrentSpan()?->spanId,
        );

        $span->attribute('http.method', $request->method());
        $span->attribute('http.url', (string) $request->url());

        try {
            $response = $next($request);

            $span->attribute('http.status_code', $response->status());

            return $response;
        } catch (\Throwable $e) {
            $span->attribute('error', true);
            $span->attribute('error.message', $e->getMessage());
            $span->setStatus('error');
            throw $e;
        } finally {
            $this->tracingManager->finishSpan($span);
        }
    }
}