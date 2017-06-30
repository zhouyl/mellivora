<?php

namespace Mellivora\Support\Providers;

use Mellivora\Config\Accessor;
use Mellivora\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container['config'] = function ($container) {
            return new Accessor([
                'paths' => [
                    root_path('config'),
                    root_path('config/' . $container['settings']['environment']),
                ],
            ]);
        };
    }

}
