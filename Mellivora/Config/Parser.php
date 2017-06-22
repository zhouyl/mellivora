<?php

namespace Mellivora\Config;

use Mellivora\Support\Fluent;

/**
 * 使用原生数组构建的配置基础类
 */
class Parser extends Fluent
{

    /**
     * Set the value at the given offset.
     *
     * @param  string $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        parent::offsetSet($offset, is_array($value) ? new self($value) : $value);
    }

    /**
     * Convert the Fluent instance to an array.
     *
     * @return array
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
