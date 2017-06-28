<?php

namespace Mellivora\Cache;

/**
 * 缓存连接类标准接口
 */
interface ConnectorInterface
{

    /**
     * 返回 psr-6 标准 cache 适配器
     *
     * @return Symfony\Component\Cache\Adapter\AbstractAdapter
     */
    public function getCacheAdapter();

    /**
     * 返回 psr-16 标准 simple-cache 接口
     *
     * @return Symfony\Component\Cache\Simple\AbstractCache
     */
    public function getSimpleCacheAdapter();

}
