<?php

namespace Mellivora\Cache;

use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Simple\RedisCache;

/**
 * redis 缓存连接器
 *
 * @link https://symfony.com/doc/current/components/cache/adapters/redis_adapter.html
 */
class RedisConnector implements ConnectorInterface
{

    /**
     * 配置参数
     *
     * @var array
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

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheAdapter()
    {
        $client = RedisAdapter::createConnection(
            $this->config['dsn'], $this->config['options']);

        return new RedisAdapter(
            $client, $this->config['namespace'], $this->config['lifetime']);
    }

    /**
     * {@inheritdoc}
     */
    public function getSimpleCacheAdapter()
    {
        $client = RedisCache::createConnection(
            $this->config['dsn'], $this->config['options']);

        return new RedisCache(
            $client, $this->config['namespace'], $this->config['lifetime']);
    }

}
