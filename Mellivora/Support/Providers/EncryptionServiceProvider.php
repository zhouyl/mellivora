<?php

namespace Mellivora\Support\Providers;

use Mellivora\Encryption\Crypt;
use Mellivora\Support\Providers\ServiceProvider;

class EncryptionServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container['encryption'] = function ($container) {
            $config = $container['config']->get('security.crypt');

            return new Crypt($config->key, $config->cipher, $config->padding);
        };
    }

}
