<?php

namespace Aftermath\Event;

use Illuminate\Support\Facades\Config;

final readonly class ExceptionEvent
{
    public function __construct(
        public readonly \Throwable $throwable,
    ) {}

    public function toArray(): array
    {
        return [
            'event_id' => (string) str()->uuid(),

            'type' => 'exception',

            'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),

            'environment' => Config::get('aftermath.environment'),

            'payload' => [
                'level' => 'error',

                'exception' => [
                    'values' => $this->exceptions(),
                ],

                'metadata' => [
                    'contexts' => [
                        'runtime' => [
                            'name' => 'PHP',
                            'version' => PHP_VERSION,
                        ],
                    ],
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