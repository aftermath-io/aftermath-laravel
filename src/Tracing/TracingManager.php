<?php 

namespace Aftermath\Tracing;

use JsonSerializable;
use Aftermath\Tracing\Span;
use Aftermath\Tracing\Trace;
use Aftermath\Transport\HttpTransport;

abstract class TracingManager
{
    abstract public function startSpan(string $name, string $kind, ?string $parentSpanId = null): Span;

    abstract public function finishSpan(Span $span): void;

    abstract public function getCurrentTrace(): ?Trace;

    abstract public function getCurrentSpan(): ?Span;

    public function flush(): void
    {
        $trace = $this->getCurrentTrace();

        if ($trace === null) {
            return;
        }

        // based on the set sample rate determine if we should send the trace or not
        $sampleRate = config('aftermath.tracing.sample_rate', 1.0);
        if (mt_rand() / mt_getrandmax() > $sampleRate) {
            return;
        }

        app(HttpTransport::class)->sendTrace($trace->jsonSerialize());
    }   
}