<?php

namespace Aftermath\Facade;

use Illuminate\Support\Facades\Facade;

final class Aftermath extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'aftermath';
    }
}