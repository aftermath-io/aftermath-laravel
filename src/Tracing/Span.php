<?php

namespace Aftermath\Tracing;

use JsonSerializable;

final class Span implements JsonSerializable
{
    public function __construct(
        public readonly string $traceId,
        public readonly string $spanId,
        public readonly ?string $parentSpanId,
        public readonly string $status,
        public readonly string $name,
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

    public function jsonSerialize(): array
    {
        return [
            'traceId' => $this->traceId,
            'spanId' => $this->spanId,
            'parentSpanId' => $this->parentSpanId,
            'name' => $this->name,
            'status' => $this->status,
            'kind' => $this->kind,
            'startedAt' => $this->startedAt,
            'finishedAt' => $this->finishedAt,
            'attributes' => $this->attributes,
        ];
    }
}
  