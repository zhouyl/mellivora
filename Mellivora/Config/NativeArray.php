<?php

namespace Mellivora\Config;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Mellivora\Support\Arr;
use Mellivora\Support\Traits\MagicAccess;

/**
 * 使用原生数组构建的配置基础类
 */
class NativeArray implements ArrayAccess, IteratorAggregate
{

    use MagicAccess;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * 构造方法
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $index => $value) {
            $this->set($index, $value);
        }
    }

    /**
     * 设定配置数据
     *
     * @param  string                          $key
     * @param  mixed                           $value
     * @return \Mellivora\Config\NativeArray
     */
    public function set($key, $value = null)
    {
        $data = is_array($key) ? $key : [$key => $value];

        foreach ($data as $key => $value) {
            Arr::set($this->config, $key, is_array($value) ? new self($value) : $value);
        }

        return $this;
    }

    /**
     * 获取配置数据
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $data = Arr::get($this->config, $key);

        if ($data === null) {
            $data = is_array($default) ? new self($default) : $default;
        }

        return $data;
    }

    /**
     * 判断 key 是否存在
     *
     * @param  string    $key
     * @return boolean
     */
    public function exists($key)
    {
        return Arr::exists($this->config, $key);
    }

    /**
     * 删除配置数据
     *
     * @param  string                          $key
     * @return \Mellivora\Config\NativeArray
     */
    public function remove($key)
    {
        Arr::forget($this->config, $key);

        return $this;
    }

    /**
     * 获取一个外部迭代器，以便 foreach 输出 config
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->config);
    }

    /**
     * 将配置数据转换为数组格式
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];

        foreach ($this->config as $key => $value) {
            if ($value instanceof self) {
                $value = $value->toArray();
            }

            $data[$key] = $value;
        }

        return $data;
    }
}
