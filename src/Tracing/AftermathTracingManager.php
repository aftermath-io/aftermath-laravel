<?php
 
namespace Aftermath\Tracing;

use JsonSerializable;
use Aftermath\Tracing\Span;
use Aftermath\Tracing\Trace;
use Illuminate\Support\Str;

class AftermathTracingManager extends TracingManager
{
    /** @var list<Span> */
    private array $spanStack;

    public function __construct(
        private ?Trace $currentTrace = null,
        private ?Span $currentSpan = null,
    ) {
        $this->spanStack = $currentSpan === null ? [] : [$currentSpan];
    }

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

        $this->spanStack[] = $span;
        $this->currentSpan = $span;

        $this->currentTrace->addSpan($span);

        return $span;
    }

    public function finishSpan(Span $span): void
    {
        $span->finish();

        foreach ($this->spanStack as $index => $activeSpan) {
            if ($activeSpan === $span) {
                unset($this->spanStack[$index]);
                break;
            }
        }

        $this->spanStack = array_values($this->spanStack);
        $this->currentSpan = $this->spanStack === []
            ? null
            : $this->spanStack[array_key_last($this->spanStack)];
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