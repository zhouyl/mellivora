<?php

namespace Mellivora\Support\Facades;

/**
 * @see \Symfony\Component\Cache\Adapter\AbstractAdapter
 */
class Cache extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cache';
    }
}
