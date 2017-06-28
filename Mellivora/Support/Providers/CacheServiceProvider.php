<?php

namespace Mellivora\Support\Providers;

use Mellivora\Cache\Manager;

class CacheServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container['cache.manager'] = function ($container) {
            $config = $container['config']->get('cache');

            $manager = new Manager($config->drivers->toArray());

            // 设置默认缓存驱动
            $manager->setDefault($config->default);

            // 设置日志处理器
            $manager->setLogger($container['logger']->get($config->logger));

            return $manager;
        };

        $this->container['cache'] = function ($container) {
            return $container['cache.manager']->getDefaultCache();
        };

        $this->container['cache.simple'] = function ($container) {
            return $container['cache.manager']->getDefaultSimpleCache();
        };
    }
}
