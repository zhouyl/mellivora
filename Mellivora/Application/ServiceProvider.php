<?php

namespace Mellivora\Application;

abstract class ServiceProvider
{

    protected $container;

    protected $providers = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function register()
    {
        //
    }

}
