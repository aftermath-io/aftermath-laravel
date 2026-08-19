<?php

namespace Aftermath\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AftermathTracingMiddleware 
{
    public function __construct(
        private readonly \Aftermath\Tracing\TracingManager $tracingManager,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $span = $this->tracingManager->startSpan(
            name: sprintf('%s %s', $request->method(), $request->path()),
            kind: 'server',
        );

        try {
            $response = $next($request);

            if ($route = $request->route()) {
                $uri = $route->uri();

                $span->setName(
                    sprintf('%s /%s', $request->method(), $uri)
                );
            }

            $span->attribute(
                'http.status_code',
                $response->getStatusCode()
            );
        } catch (\Throwable $e) {
            $this->tracingManager->getCurrentSpan()?->attribute('exception', $e::class);

            throw $e;
        } finally {
            $this->tracingManager->getCurrentSpan()?->finish();
        }
        
        return $response;
    }
}