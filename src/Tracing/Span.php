<?php

namespace Aftermath\Tracing;

use JsonSerializable;
use Carbon\Carbon;

final class Span implements JsonSerializable
{
    public function __construct(
        public readonly string $traceId,
        public readonly string $spanId,
        public readonly ?string $parentSpanId,
        public readonly string $status,
        public string $name,
        public readonly string $kind,
        public readonly float $startedAt,
        private array $attributes = [],
    ) {}

    private ?float $finishedAt = null;

    public function attribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function finish(): void
    {
        $this->finishedAt ??= microtime(true);
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setStartedAt(float $startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    public function jsonSerialize(): array
    {
        return [
            'trace_id' => $this->traceId,
            'span_id' => $this->spanId,
            'parent_span_id' => $this->parentSpanId,
            'name' => $this->name,
            'status' => $this->status,
            'kind' => $this->kind,
            'started_at' => $this->startedAt,
            'finished_at' => $this->finishedAt,
            'attributes' => $this->attributes,
        ];
    }
}
