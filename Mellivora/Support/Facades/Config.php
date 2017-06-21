<?php

namespace Mellivora\Support\Facades;

/**
 * @see Mellivora\Config\Accessor
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
