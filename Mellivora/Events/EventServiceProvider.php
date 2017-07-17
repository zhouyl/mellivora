<?php

namespace Mellivora\Events;

use Mellivora\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container['events'] = function ($container) {
            return new Dispatcher($container);
        };
    }
}
