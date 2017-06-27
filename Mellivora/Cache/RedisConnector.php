<?php

namespace Mellivora\Cache;

use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Simple\RedisCache;

class RedisConnector implements ConnectorInterface
{

    /**
     * @var array
     * @link https://symfony.com/doc/current/components/cache/adapters/redis_adapter.html
     */
    protected $config = [

        // 缓存命名空间，用于项目隔离 (30天)
        'namespace' => '',

        // 默认的缓存生命周期
        'lifetime'  => 0,

        // 格式: redis://[user:pass@][ip|host|socket[:port]][/db-index]
        'dsn'       => 'redis://127.0.0.1:6379',

        // @link https://symfony.com/doc/current/components/cache/adapters/redis_adapter.html#configure-the-options
        'options'   => [],

    ];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getCacheAdapter()
    {
        $client = RedisAdapter::createConnection(
            $this->config['dsn'], $this->config['options']);

        return new RedisAdapter(
            $client, $this->config['namespace'], $this->config['lifetime']);
    }

    public function getSimpleCacheAdapter()
    {
        $client = RedisCache::createConnection(
            $this->config['dsn'], $this->config['options']);

        return new RedisCache(
            $client, $this->config['namespace'], $this->config['lifetime']);
    }

}
