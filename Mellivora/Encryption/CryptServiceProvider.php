<?php

namespace Mellivora\Encryption;

use Mellivora\Support\ServiceProvider;

class CryptServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container['crypt'] = function ($container) {
            $config = $container['config']->get('security.crypt');

            return new Crypt($config->key, $config->cipher, $config->padding);
        };
    }

}
