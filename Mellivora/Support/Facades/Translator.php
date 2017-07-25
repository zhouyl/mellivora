<?php

namespace Mellivora\Support\Facades;

/**
 * @see \Mellivora\Translation\Translator
 */
class Translator extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'translator';
    }
}
