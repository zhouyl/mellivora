<?php

namespace Mellivora\Console;

use Mellivora\Container\Container;

trait DetectsApplicationNamespace
{
    /**
     * Get the application namespace.
     *
     * @return string
     */
    protected function getAppNamespace()
    {
        return Container::getInstance()->getNamespace();
    }
}
