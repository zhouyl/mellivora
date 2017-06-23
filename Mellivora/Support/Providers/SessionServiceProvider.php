<?php

namespace Mellivora\Support\Providers;

use Mellivora\Session\Session;
use Mellivora\Support\Providers\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container['session'] = function ($container) {
            $session = new Session;
            $session->start();

            return $session;
        };
    }

}
