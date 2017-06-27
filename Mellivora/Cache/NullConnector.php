<?php

namespace Mellivora\Cache;

use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Simple\NullCache;

class NullConnector implements ConnectorInterface
{

    public function getCacheAdapter()
    {
        return new NullAdapter;
    }

    public function getSimpleCacheAdapter()
    {
        return new NullCache;
    }

}
