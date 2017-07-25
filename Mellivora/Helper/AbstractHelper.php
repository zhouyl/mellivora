<?php

namespace Mellivora\Helper;

use Mellivora\Application\Container;

/**
 * helper 助手基类
 */
class AbstractHelper
{
    /**
     * @param \Mellivora\Application\Container $container
     */
    protected $container;

    /**
     * @param \Mellivora\Application\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
}
