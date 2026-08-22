<?php

namespace Aftermath;

use Aftermath\Event\AbstractEvent;
use Aftermath\EventBuffer;

class AftermathEventBuffer implements EventBuffer
{
    protected $events = [];

    public function push(AbstractEvent $event): void
    {
        $this->events[] = $event;
    }

    public function pull(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    public function isEmpty(): bool
    {
        return $this->events === [];
    }
}