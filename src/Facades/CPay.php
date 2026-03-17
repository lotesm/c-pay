<?php

namespace CPay\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CPay\CPay
 */
class CPay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CPay\CPay::class;
    }
}
