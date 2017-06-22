<?php

namespace Mellivora\Support\Providers;

use Mellivora\Application\Container;

/**
 * 服务提供者基类
 */
abstract class ServiceProvider
{

    /**
     * @param Mellivora\Application\Container $container
     */
    protected $container;

    /**
     * @param array
     */
    protected $providers = [];

    /**
     * @param Mellivora\Application\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

}
