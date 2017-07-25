<?php

namespace Mellivora\Support\Facades;

/**
 * @see \Mellivora\Session\Session
 */
class Session extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'session';
    }
}
