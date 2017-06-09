<?php

namespace Mellivora\Support\Facades;

/**
 * @see Mellivora\Application\App
 */
class App extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return Mellivora\Application\App
     */
    protected static function getFacadeAccessor()
    {
        return parent::getFacadeApplication();
    }
}
