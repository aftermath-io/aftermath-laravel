<?php
 
namespace Aftermath\Tracing;

use JsonSerializable;
use Aftermath\Tracing\Span;
use Aftermath\Tracing\Trace;
use Illuminate\Support\Str;

class AftermathTracingManager extends TracingManager
{
    public function __construct(
        private ?Trace $currentTrace = null,
        private ?Span $currentSpan = null,
    ) {}

    public function startSpan(string $name, string $kind, ?string $parentSpanId = null): Span
    {
        if ($this->currentTrace === null) {
            $this->currentTrace = Trace::start();
        }

        $spanId = Str::uuid()->toString();
        $span = new Span(
            traceId: $this->currentTrace->getId(),
            spanId: $spanId,
            parentSpanId: $parentSpanId,
            status: 'ok',
            name: $name,
            kind: $kind,
            startedAt: microtime(true),
        );

        $this->currentSpan = $span;

        return $span;
    }

    public function finishSpan(Span $span): void
    {
        $span->finish();

        if ($this->currentSpan === $span) {
            $this->currentSpan = null;
        }
    }

    public function getCurrentTrace(): ?Trace
    {
        return $this->currentTrace;
    }

    public function getCurrentSpan(): ?Span
    {
        return $this->currentSpan;
    }
}