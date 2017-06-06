<?php

namespace Mellivora\Config;

/**
 * 使用原生数组构建的配置基础类
 */
class NativeArray implements \ArrayAccess, \Countable
{
    /**
     * 构造方法
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $index => $value) {
            $this->offsetSet($index, $value);
        }
    }

    /**
     * 设定 key-value 数据
     *
     * @param string $index
     * @param mixed  $value
     */
    public function offsetSet($index, $value)
    {
        $index = strval($index);

        $this->$index = is_array($value) ? new self($value) : $value;
    }

    /**
     * 获取 key-value 数据
     *
     * @param  string       $index
     * @return mixed|null
     */
    public function offsetGet($index)
    {
        $index = strval($index);

        return $this->offsetExists($index) ? $this->$index : null;
    }

    /**
     * 判断 key 是否存在
     *
     * @param  string    $index
     * @return boolean
     */
    public function offsetExists($index)
    {
        $index = strval($index);

        return isset($this->$index);
    }

    /**
     * 删除 key-value 数据
     *
     * @param string $index
     */
    public function offsetUnset($index)
    {
        $index = strval($index);

        $this->$index = null;
    }

    /**
     * 合并两个配置数据
     *
     * @param  self     $config
     * @param  object   $instance
     * @return object
     */
    public function merge(self $config, $instance = null)
    {
        if (!is_object($instance)) {
            $instance = $this;
        }

        $number = $instance->count();
        foreach (get_object_vars($config) as $key => $value) {
            if (isset($instance->$key)) {
                $localObject = $instance->$key;

                if ($localObject instanceof self && $value instanceof self) {
                    $this->merge($value, $localObject);
                    continue;
                }
            }

            if (is_numeric($key)) {
                $key = strval($number);
                $number++;
            }

            $instance->$key = $value;
        }

        return $instance;
    }

    /**
     * 将所有的配置数据转换为数组格式
     *
     * 如果配置数据为对象，且拥有 toArray/getArrayCopy/asArray/as_array 方法
     * 将调整数据对像的数组转换方法进行转换
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];

        foreach (get_object_vars($this) as $key => $value) {
            if (is_object($value)) {
                if (method_exists($value, 'toArray')) {
                    $value = $value->toArray();
                } elseif (method_exists($value, 'getArrayCopy')) {
                    $value = $value->getArrayCopy();
                } elseif (method_exists($value, 'asArray')) {
                    $value = $value->asArray();
                } elseif (method_exists($value, 'as_array')) {
                    $value = $value->as_array();
                } else {
                    $value = get_object_vars($value);
                }
            }

            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * 统计 key-value 的数量
     *
     * @return integer
     */
    public function count()
    {
        return count(get_object_vars($this));
    }

    /**
     * 根据 path 路径，获取配置数据
     *
     * path 的格式需要使用 “.” 来进行标识
     *
     * <code>
     * $config = new Mellivora\Config\NativeArray(['db' => ['host' => 'localhost']]);
     *
     * print_r($config->get('db.host')); // localhost
     * </code>
     *
     * @param  string   $path
     * @param  mixed    $default
     * @return mixed
     */
    public function get($path, $default = null)
    {
        if ($this->offsetExists($path)) {
            return $this->offsetGet($path);
        }

        $path = trim($path, '.');
        if (strpos($path, '.') === false) {
            return $default;
        }

        $data = $this->toArray();
        foreach (explode('.', $path) as $key) {
            if (array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                return $default;
            }
        }

        return $data;
    }
}
