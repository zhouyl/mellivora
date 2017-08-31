<?php

namespace Mellivora\Cache;

use Mellivora\Cache\Manager;
use Mellivora\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

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
            $logger = value($config->logger);
            if ($logger instanceof LoggerInterface) {
                $manager->setLogger();
            }

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
