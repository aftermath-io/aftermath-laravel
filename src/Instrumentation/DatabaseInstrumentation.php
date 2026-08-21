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
        $span = $this->tracingManager->startSpan(
            name: $event->sql,
            kind: 'database',
            parentSpanId: $this->tracingManager->getCurrentSpan()?->spanId,
        );

        $span->setStartedAt(microtime(true) - ($event->time / 1000));

        $span->attribute('db.system', $event->connection->getDriverName());
        $span->attribute('db.name', $event->connection->getDatabaseName());
        $span->attribute('db.connection_name', $event->connectionName);
        $span->attribute('db.statement', $event->sql);
        $span->attribute('db.duration_ms', $event->time);

        $this->tracingManager->finishSpan($span);
    }
}
