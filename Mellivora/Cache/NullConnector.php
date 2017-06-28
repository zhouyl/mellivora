<?php

namespace Mellivora\Cache;

use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Simple\NullCache;

/**
 * 这是一个空的缓存连接器(未使用任何缓存)
 */
class NullConnector extends Connector
{

    /**
     * {@inheritdoc}
     */
    public function getCacheAdapter()
    {
        return new NullAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function getSimpleCacheAdapter()
    {
        return new NullCache;
    }

}
