<?php

namespace Mellivora\Cache;

use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Simple\MemcachedCache;

/**
 * memcached 缓存连接器
 *
 * @see https://symfony.com/doc/current/components/cache/adapters/memcached_adapter.html
 */
class MemcachedConnector extends Connector
{
    /**
     * 配置参数
     *
     * @var array
     */
    protected $config = [
        // 格式: memcached:/[user:pass@][ip|host|socket[:port]][?weight=int]
        'servers' => [
            'memcached://127.0.0.1:11211',
        ],

        // @link https://symfony.com/doc/current/components/cache/adapters/memcached_adapter.html#configure-the-options
        // @link http://php.net/manual/zh/memcached.setoptions.php
        // @link http://php.net/manual/zh/memcached.constants.php
        'options' => [],
    ];

    /**
     * {@inheritdoc}
     */
    public function getCacheAdapter()
    {
        $client = MemcachedAdapter::createConnection(
            $this->config['servers'],
            $this->config['options']
        );

        return new MemcachedAdapter(
            $client,
            $this->config['namespace'],
            $this->config['lifetime']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSimpleCacheAdapter()
    {
        $client = MemcachedCache::createConnection(
            $this->config['servers'],
            $this->config['options']
        );

        return new MemcachedCache(
            $client,
            $this->config['namespace'],
            $this->config['lifetime']
        );
    }
}
