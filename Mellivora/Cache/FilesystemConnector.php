<?php

namespace Mellivora\Cache;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Simple\FilesystemCache;

/**
 * 文件系统缓存连接器
 *
 * @link https://symfony.com/doc/current/components/cache/adapters/filesystem_adapter.html
 */
class FilesystemConnector extends Connector
{
    /**
     * 配置参数
     *
     * @var array
     */
    protected $config = [
        'directory' => null, // 缓存目录
    ];

    /**
     * {@inheritdoc}
     */
    public function getCacheAdapter()
    {
        return new FilesystemAdapter(
            $this->config['namespace'],
            $this->config['lifetime'],
            $this->config['directory']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSimpleCacheAdapter()
    {
        return new FilesystemCache(
            $this->config['namespace'],
            $this->config['lifetime'],
            $this->config['directory']
        );
    }

}
