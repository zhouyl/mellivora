<?php

namespace Mellivora\Support\Providers;

use Mellivora\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container['database'] = function ($container) {};
    }

}
