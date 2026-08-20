<?php

namespace Aftermath\Transport;

interface Transport
{
    public function sendEvent(array $event): void;
    public function sendTrace(array $trace): void;
}