<?php

namespace Aftermath\Tracing;

use Illuminate\Support\Str;
use JsonSerializable;

class Trace implements JsonSerializable
{
    public function __construct(
        public readonly string $traceId,
        public readonly float $startedAt,
        /** @var Span[] */
        private array $spans = [],
    ) {}

    public static function start(): self
    {
        return new self(
            traceId: Str::uuid()->toString(),
            startedAt: microtime(true),
        );
    }

    public function getId(): string
    {
        return $this->traceId;
    }

    public function jsonSerialize(): array
    {
        return [
            'trace_id' => $this->traceId,
            'started_at' => $this->startedAt,
            'spans' => array_map(fn (Span $span) => $span->jsonSerialize(), $this->spans),
        ];
    }
}