<?php

namespace Mellivora\Support\Traits;

trait MagicAccess
{

    /********************************************************************
     * ArrayAccess 接口实现
     ********************************************************************/

    public function offsetSet($key, $value)
    {
        return $this->set($key, $value);
    }

    public function offsetGet($key)
    {
        return $this->get($key);
    }

    public function offsetExists($key)
    {
        return $this->has($key);
    }

    public function offsetUnset($key)
    {
        return $this->delete($key);
    }

    /********************************************************************
     * 魔术方法实现
     ********************************************************************/

    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __isset($key)
    {
        return $this->has($key);
    }

    public function __unset($key)
    {
        return $this->delete($key);
    }
}
