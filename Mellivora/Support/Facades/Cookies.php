<?php

namespace Mellivora\Support\Facades;

/**
 * @see Mellivora\Http\Cookies
 */
class Cookies extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cookies';
    }
}
