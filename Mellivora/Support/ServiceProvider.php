<?php

namespace Mellivora\Support;

use Mellivora\Application\App;

/**
 * 服务提供者基类
 */
abstract class ServiceProvider
{

    /**
     * @param Mellivora\Application\App $app
     */
    protected $app;

    /**
     * @param Mellivora\Application\Container $container
     */
    protected $container;

    /**
     * @param Mellivora\Application\App $app
     */
    public function __construct(App $app)
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
