<?php

namespace Adequaat\LaravelPostnlApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Adequaat\LaravelPostnlApi\LaravelPostnlApi
 */
class LaravelPostnlApi extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Adequaat\LaravelPostnlApi\LaravelPostnlApi::class;
    }
}
