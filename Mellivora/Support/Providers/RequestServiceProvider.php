<?php

namespace Mellivora\Support\Providers;

use Mellivora\Http\Request;
use Mellivora\Support\ServiceProvider;

class RequestServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container['request'] = function ($container) {
            return Request::createFromEnvironment($container['environment']);
        };
    }

}
