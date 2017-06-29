<?php

namespace Mellivora\Support\Middlewares;

use Mellivora\Application\App;

abstract class Middleware
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
     * Register the middleware.
     *
     * @return void
     */
    public function register()
    {
        //
    }

}
