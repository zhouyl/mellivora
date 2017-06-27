<?php

namespace Mellivora\Cache;

interface ConnectorInterface
{

    public function getCacheAdapter();

    public function getSimpleCacheAdapter();

}
