<?php

namespace Mellivora\Config;

use Mellivora\Support\Arr;
use Mellivora\Support\Fluent;

/**
 * 使用原生数组构建的配置基础类
 */
class NativeArray extends Fluent
{

    /**
     * {@inheritdoc}
     */
    public function set($key, $value = null)
    {
        $attributes = is_array($key) ? $key : [$key => $value];

        foreach ($attributes as $key => $value) {
            $value = is_array($value) ? new self($value) : $value;

            Arr::set($this->attributes, $key, $value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $data = [];

        foreach (parent::toArray() as $key => $value) {
            if ($value instanceof self) {
                $value = $value->toArray();
            }

            $data[$key] = $value;
        }

        return $data;
    }
}
