<?php

namespace Mellivora\Support\Facades;

/**
 * @see \Mellivora\Database\Schema\Builder
 */
class Schema extends Facade
{
    /**
     * Get a schema builder instance for a connection.
     *
     * @param  string                               $name
     * @return \Mellivora\Database\Schema\Builder
     */
    public static function connection($name)
    {
        return static::$app->getContainer()->get('db')->connection($name)->getSchemaBuilder();
    }

    /**
     * Get a schema builder instance for the default connection.
     *
     * @return \Mellivora\Database\Schema\Builder
     */
    protected static function getFacadeAccessor()
    {
        return static::$app->getContainer()->get('db')->connection()->getSchemaBuilder();
    }
}
