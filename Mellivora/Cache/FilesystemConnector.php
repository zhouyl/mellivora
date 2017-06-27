<?php

namespace Mellivora\Cache;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Simple\FilesystemCache;

class FilesystemConnector implements ConnectorInterface
{

    /**
     * @var array
     * @link https://symfony.com/doc/current/components/cache/adapters/filesystem_adapter.html
     */
    protected $config = [
        'directory' => null, // 缓存目录
    ];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getCacheAdapter()
    {
        return new FilesystemAdapter(
            $this->config['namespace'],
            $this->config['lifetime'],
            $this->config['directory']
        );
    }

    public function getSimpleCacheAdapter()
    {
        return new FilesystemCache(
            $this->config['namespace'],
            $this->config['lifetime'],
            $this->config['directory']
        );
    }

}
