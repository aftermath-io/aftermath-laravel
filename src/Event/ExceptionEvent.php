<?php

namespace Aftermath\Event;

final readonly class ExceptionEvent extends AbstractEvent
{
    public function __construct(
        public readonly \Throwable $throwable,
    ) {}

    protected function type(): string
    {
        return 'exception';
    }

    protected function payload(): array
    {
        return [
            'level' => 'error',

            'exception' => [
                'values' => $this->exceptions(),
            ],

            'metadata' => [
                'contexts' => [
                    'runtime' => $this->runtimeContext(),
                ],
            ],
        ];
    }

    private function exceptions(): array
    {
        $exceptions = [];
        $throwable = $this->throwable;

        do {
            $exceptions[] = [
                'type' => $throwable::class,
                'message' => $throwable->getMessage(),
                'mechanism' => [
                    'type' => 'generic',
                    'handled' => false,
                ],
                'handled' => false,
                'stacktrace' => [
                    'frames' => $this->stacktrace($throwable),
                ],
            ];

            $throwable = $throwable->getPrevious();
        } while ($throwable !== null);

        return $exceptions;
    }

    private function stacktrace(\Throwable $throwable): array
    {
        $frames = array_merge([
            [
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
            ],
        ], $throwable->getTrace());

        return array_map(static function (array $frame): array {
            return array_filter([
                'filename' => $frame['file'] ?? null,
                'function' => $frame['function'] ?? null,
                'module' => $frame['class'] ?? null,
                'lineno' => $frame['line'] ?? null,
                'colno' => $frame['column'] ?? null,
            ], static fn (mixed $value): bool => $value !== null);
        }, $frames);
    }
}