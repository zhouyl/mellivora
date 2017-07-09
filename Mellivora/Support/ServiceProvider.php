<?php

namespace Mellivora\Support;

/**
 * 服务提供者基类
 */
abstract class ServiceProvider
{

    /**
     * @param object $app
     */
    protected $app;

    /**
     * @param Mellivora\Application\Container $container
     */
    protected $container;

    /**
     * @param object $app
     */
    public function __construct($app)
    {
        $this->app       = $app;
        $this->container = $app->getContainer();
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
