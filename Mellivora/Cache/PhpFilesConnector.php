<?php

namespace Mellivora\Cache;

use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Simple\PhpFilesCache;

/**
 * php 文件缓存连接器
 *
 * 使用 php 文件返回 array 的方式来进行缓存
 *
 * @link https://symfony.com/doc/current/components/cache/adapters/phpfiles_adapter.html
 */
class PhpFilesConnector implements ConnectorInterface
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
        return new PhpFilesAdapter($this->config['namespace'],
            $this->config['lifetime'], $this->config['directory']);
    }

    /**
     * {@inheritdoc}
     */
    public function getSimpleCacheAdapter()
    {
        return new PhpFilesCache($this->config['namespace'],
            $this->config['lifetime'], $this->config['directory']);
    }

}
