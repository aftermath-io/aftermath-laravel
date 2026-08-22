<?php

namespace Aftermath\Instrumentation;

use Aftermath\Tracing\TracingManager;
use Aftermath\Instrumentation\Instrumentation;
use Illuminate\Support\Facades\Http;

class HttpInstrumentation implements Instrumentation
{
    public function __construct(
        protected readonly TracingManager $tracingManager,
    )
    {
    }

    public function boot(): void
    {
        Http::globalRequestMiddleware(fn ($request) => $this->requestMiddleware($request));
        Http::globalResponseMiddleware(fn ($response) => $this->responseMiddleware($response));
    }

    public function requestMiddleware($request)
    {
        $this->tracingManager->startSpan(
            name: $request->getMethod() . ' ' . $request->getUri(),
            kind: 'http',
            parentSpanId: $this->tracingManager->getCurrentSpan()?->spanId,
        )
            ->attribute('http.method', $request->getMethod());

        return $request;
    }

    public function responseMiddleware($response)
    {
        $this->tracingManager->getCurrentSpan()
            ->attribute('http.status_code', $response->getStatusCode());

        $this->tracingManager->finishCurrentSpan();

        return $response;
    }
}
