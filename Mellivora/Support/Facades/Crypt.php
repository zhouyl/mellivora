<?php

namespace Mellivora\Support\Facades;

/**
 * @see \Mellivora\Encryption\Crypt
 */
class Crypt extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'crypt';
    }
}
