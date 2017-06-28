<?php

namespace Mellivora\Cache;

use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Exception\InvalidArgumentException;

/**
 * 缓存连接器管理类
 *
 * @link https://symfony.com/doc/current/components/cache.html
 * @link https://symfony.com/doc/current/components/cache/cache_pools.html
 */
class Manager
{

    /**
     * 默认缓存名称
     *
     * @var string
     */
    protected $default = 'null';

    /**
     * 日志处理器
     *
     * @var Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * 支持的缓存驱动
     *
     * 格式为：[缓存名 => 驱动参数]
     *
     * @var array
     */
    protected $drivers = [
        'null' => [
            'connector' => NullConnector::class,
        ],
    ];

    /**
     * 已实例化的缓存连接器实例
     *
     * @var array
     */
    protected $connectors = [];

    /**
     * Constructor
     *
     * @param array $drivers
     */
    public function __construct(array $drivers = [])
    {
        $this->setDrivers($drivers);
    }

    /**
     * 设定默认的缓存名称
     *
     * @param  string                                                       $name
     * @throws Symfony\Component\Cache\Exception\InvalidArgumentException
     * @return Mellivora\Cache\Manager
     */
    public function setDefault($name)
    {
        $name = strtolower($name);

        if (!isset($this->drivers[$name])) {
            throw new InvalidArgumentException(
                "Unregistered cache driver name '$name'");
        }

        $this->default = $name;

        return $this;
    }

    /**
     * 获取默认的缓存名称
     *
     * @return string
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * 批量设定缓存驱动配置
     *
     * @param  array                     $drivers
     * @return Mellivora\Cache\Manager
     */
    public function setDrivers(array $drivers)
    {
        foreach ($drivers as $name => $config) {
            $this->setDriver($name, $config);
        }

        return $this;
    }

    /**
     * 设定缓存驱动配置
     *
     * @param  string                    $name
     * @param  array                     $config
     * @return Mellivora\Cache\Manager
     */
    public function setDriver($name, array $config)
    {
        if (!isset($config['connector'])) {
            $config['connector'] = NullConnector::class;
        }

        $this->drivers[strtolower($name)] = $config;

        return $this;
    }

    /**
     * 设置日志处理器
     *
     * @param Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * 根据名称获取缓存构造器
     *
     * @param  string                                                       $name
     * @throws Symfony\Component\Cache\Exception\InvalidArgumentException
     * @return Mellivora\Cache\Connector
     */
    protected function getConnector($name)
    {
        if (!$config = $this->drivers[$name] ?? false) {
            throw new InvalidArgumentException(
                "Unregistered cache driver name '$name'");
        }

        if (!isset($this->connectors[$name])) {
            if (!is_subclass_of($config['connector'], Connector::class)) {
                throw new InvalidArgumentException(
                    $config['connector'] . ' must implement of ' . Connector::class);
            }

            $connector = new $config['connector']($config);

            $this->connectors[$name] = $connector;
        }

        return $this->connectors[$name];
    }

    /**
     * 获取 psr-6 标准 cache 适配器
     *
     * @param  string                                            $name
     * @return Symfony\Component\Cache\Adapter\AbstractAdapter
     */
    public function getCache($name)
    {
        $cache = $this->getConnector($name)->getCacheAdapter();

        if ($this->logger instanceof LoggerInterface) {
            $cache->setLogger($this->logger);
        }

        return $cache;
    }

    /**
     * 获取 psr-16 标准 simple-cache 适配器
     *
     * @param  string                                         $name
     * @return Symfony\Component\Cache\Simple\AbstractCache
     */
    public function getSimpleCache($name)
    {
        $cache = $this->getConnector($name)->getSimpleCacheAdapter();

        if ($this->logger instanceof LoggerInterface) {
            $cache->setLogger($this->logger);
        }

        return $cache;
    }

    /**
     * 获取默认的 psr-6 标准 cache 适配器
     *
     * @return Symfony\Component\Cache\Adapter\AbstractAdapter
     */
    public function getDefaultCache()
    {
        return $this->getCache($this->default);
    }

    /**
     * 获取 默认的 psr-16 标准 simple-cache 适配器
     *
     * @return Symfony\Component\Cache\Simple\AbstractCache
     */
    public function getDefaultSimpleCache()
    {
        return $this->getSimpleCache($this->default);
    }
}
