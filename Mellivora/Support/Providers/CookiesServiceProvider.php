<?php

namespace Mellivora\Support\Providers;

use Mellivora\Http\Cookies;
use Mellivora\Support\Providers\ServiceProvider;

class CookiesServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container['cookies'] = function ($container) {
            $config = $container['config']->get('session.cookies');

            $cookies = new Cookies($config->toArray());

            // 数据需要加密处理
            if ($config->encrypt) {
                $cookies->setEncryption($container['crypt']);
            }

            return $cookies;
        };
    }
}
