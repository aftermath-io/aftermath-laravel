<?php

namespace Aftermath\Instrumentation;

use Aftermath\Tracing\TracingManager;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\QueryExecuted;

final class DatabaseInstrumentation implements Instrumentation
{
    public function __construct(
        private Dispatcher $events,
        private readonly TracingManager $tracingManager,
    ) {
    }

    public function boot(): void
    {
        $this->events->listen(QueryExecuted::class, $this->recordQuery(...));
    }

    public function recordQuery(QueryExecuted $event): void
    {
        $this->tracingManager->startSpan(
            name: $event->sql,
            kind: 'database',
            parentSpanId: $this->tracingManager->getCurrentSpan()?->spanId,
        )
            ->setStartedAt(microtime(true) - ($event->time / 1000))
            ->attribute('db.system', $event->connection->getDriverName())
            ->attribute('db.name', $event->connection->getDatabaseName())
            ->attribute('db.connection_name', $event->connectionName)
            ->attribute('db.statement', $event->sql)
            ->attribute('db.duration_ms', $event->time)
            ->finish();
    }
}
