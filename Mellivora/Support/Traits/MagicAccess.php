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
        if (method_exists($this, 'exists')) {
            return $this->exists($key);
        }

        if (method_exists($this, 'has')) {
            return $this->has($key);
        }

        return $this->get($key) !== null;
    }

    public function offsetUnset($key)
    {
        if (method_exists($this, 'remove')) {
            return $this->remove($key);
        }

        return $this->delete($key);
    }

    /********************************************************************
     * 魔术方法实现
     ********************************************************************/

    public function __set($key, $value)
    {
        return $this->offsetSet($key, $value);
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    public function __unset($key)
    {
        return $this->offsetUnset($key);
    }
}
