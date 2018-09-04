<?php

namespace Mellivora\Cache;

use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Simple\RedisCache;

/**
 * redis 缓存连接器
 *
 * @see https://symfony.com/doc/current/components/cache/adapters/redis_adapter.html
 */
class RedisConnector extends Connector
{
    /**
     * 配置参数
     *
     * @var array
     */
    protected $config = [
        // 格式: redis://[user:pass@][ip|host|socket[:port]][/db-index]
        'dsn'     => 'redis://127.0.0.1:6379',

        // @link https://symfony.com/doc/current/components/cache/adapters/redis_adapter.html#configure-the-options
        'options' => [],
    ];

    /**
     * {@inheritdoc}
     */
    public function getCacheAdapter()
    {
        $client = RedisAdapter::createConnection(
            $this->config['dsn'],
            $this->config['options']
        );

        return new RedisAdapter(
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
        $client = RedisCache::createConnection(
            $this->config['dsn'],
            $this->config['options']
        );

        return new RedisCache(
            $client,
            $this->config['namespace'],
            $this->config['lifetime']
        );
    }
}
