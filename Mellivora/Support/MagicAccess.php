<?php

namespace Mellivora\Support;

use ArrayAccess;
use Mellivora\Support\Arr;

class MagicAccess implements ArrayAccess
{

    protected $data = [];

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }

    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
        } else {
            Arr::set($this->data, $key, $value);
        }

        return $this;
    }

    public function get($key, $default = null)
    {
        return Arr::get($this->data, $key, $default);
    }

    public function has($key)
    {
        return Arr::exists($this->data, $key);
    }

    public function delete($key)
    {
        Arr::forget($this->data, $key);

        return $this;
    }

    public function clear()
    {
        foreach ($this->data as $key => $value) {
            $this->delete($key);
        }

        return $this;
    }

    public function toArray()
    {
        return $this->data;
    }

    /********************************************************************************
     * ArrayAccess 接口实现
     *******************************************************************************/

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

    /********************************************************************************
     * 魔术方法实现
     *******************************************************************************/

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
