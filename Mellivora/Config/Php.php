<?php

namespace Mellivora\Config;

use RuntimeException;

/**
 * php 格式配置文件解释器
 */
class Php extends NativeArray
{
    /**
     * 构造方法
     *
     * @param  string               $file
     * @throws \RuntimeExceptionn
     */
    public function __construct($file)
    {
        $data = require $file;

        // 如果返回的是一个回调，则执行并返回数据
        if (is_callable($data)) {
            $data = call_user_func($data);
        }

        // 检查返回的是否为数组数据
        if (!is_array($data)) {
            throw new RuntimeException('PHP file does not return an array');
        }

        parent::__construct($data);
    }
}
