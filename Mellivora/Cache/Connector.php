<?php

namespace Mellivora\Cache;

/**
 * 缓存连接类标准接口
 */
abstract class Connector
{

    /**
     * 配置参数
     *
     * @var array
     */
    protected $defaults = [
        'namespace' => 'mellivora-cache', // 缓存命名空间，用于项目隔离
        'lifetime'  => 2592000,           // 默认的缓存生命周期 (30天)
    ];

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = array_merge($this->defaults, $this->config, $config);
    }

    /**
     * 返回 psr-6 标准 cache 适配器
     *
     * @return Symfony\Component\Cache\Adapter\AbstractAdapter
     */
    abstract public function getCacheAdapter();

    /**
     * 返回 psr-16 标准 simple-cache 接口
     *
     * @return Symfony\Component\Cache\Simple\AbstractCache
     */
    abstract public function getSimpleCacheAdapter();

}
