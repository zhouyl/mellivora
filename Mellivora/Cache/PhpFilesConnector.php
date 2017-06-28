<?php

namespace Mellivora\Cache;

use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Simple\PhpFilesCache;

/**
 * php 文件缓存连接器
 *
 * 使用 php 文件返回 array 的方式来进行缓存
 */
class PhpFilesConnector extends Connector
{

    /**
     * 配置参数
     *
     * @var array
     */
    protected $config = [
        // 缓存目录
        'directory' => null,
    ];

    /**
     * {@inheritdoc}
     */
    public function getCacheAdapter()
    {
        return new PhpFilesAdapter(
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
        return new PhpFilesCache(
            $this->config['namespace'],
            $this->config['lifetime'],
            $this->config['directory']
        );
    }

}
