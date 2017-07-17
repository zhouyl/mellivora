<?php

namespace Mellivora\Support\Facades;

/**
 * @see ellivora\Database\DatabaseManager
 */
class DB extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'db';
    }
}
