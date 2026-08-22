<?php

namespace Aftermath;

use Aftermath\Event\AbstractEvent;

interface EventBuffer
{
    public function push(AbstractEvent $event): void;

    /**
     * @returns AbstractEvent[]
     */
    public function pull(): array;

    public function isEmpty(): bool;
}