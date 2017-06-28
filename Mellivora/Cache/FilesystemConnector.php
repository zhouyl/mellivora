<?php

namespace Mellivora\Cache;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Simple\FilesystemCache;

/**
 * 文件系统缓存连接器
 *
 * @link https://symfony.com/doc/current/components/cache/adapters/filesystem_adapter.html
 */
class FilesystemConnector implements ConnectorInterface
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

        // 缓存目录
        'directory' => null,
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
        return new FilesystemAdapter($this->config['namespace'],
            $this->config['lifetime'], $this->config['directory']);
    }

    /**
     * {@inheritdoc}
     */
    public function getSimpleCacheAdapter()
    {
        return new FilesystemCache($this->config['namespace'],
            $this->config['lifetime'], $this->config['directory']);
    }

}
