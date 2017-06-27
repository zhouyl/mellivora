<?php

namespace Mellivora\Cache;

use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Simple\MemcachedCache;

class MemcachedConnector implements ConnectorInterface
{

    /**
     * @var array
     * @link https://symfony.com/doc/current/components/cache/adapters/memcached_adapter.html
     */
    protected $config = [

        // 缓存命名空间，用于项目隔离 (30天)
        'namespace' => '',

        // 默认的缓存生命周期
        'lifetime'  => 0,

        // 格式: memcached:/[user:pass@][ip|host|socket[:port]][?weight=int]
        'servers'   => [
            'memcached://127.0.0.1:11211',
        ],

        // @link https://symfony.com/doc/current/components/cache/adapters/memcached_adapter.html#configure-the-options
        // @link http://php.net/manual/zh/memcached.setoptions.php
        // @link http://php.net/manual/zh/memcached.constants.php
        'options'   => [],

    ];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getCacheAdapter()
    {
        $client = MemcachedAdapter::createConnection(
            $this->config['servers'], $this->config['options']);

        return new MemcachedAdapter(
            $client, $this->config['namespace'], $this->config['lifetime']);
    }

    public function getSimpleCacheAdapter()
    {
        $client = MemcachedCache::createConnection(
            $this->config['servers'], $this->config['options']);

        return new MemcachedCache(
            $client, $this->config['namespace'], $this->config['lifetime']);
    }

}
