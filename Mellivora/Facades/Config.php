<?php

namespace Mellivora\Facades;

/**
 * @see Mellivora\Config\Autoloader
 */
class Config extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'config';
    }
}