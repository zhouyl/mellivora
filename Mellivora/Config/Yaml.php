<?php

namespace Mellivora\Config;

use RuntimeException;

/**
 * yaml 格式配置文件解释器
 */
class Yaml extends Parser
{
    /**
     * 构造方法
     *
     * @param  string             $file
     * @param  Closure[]          $callbacks
     * @throws RuntimeException
     */
    public function __construct($file, array $callbacks = [])
    {
        if (!extension_loaded('yaml')) {
            throw new RuntimeException('Yaml extension not loaded');
        }

        if ($callbacks) {
            $ndocs = 0;
            $data  = @yaml_parse_file($file, 0, $ndocs, $callbacks);
        } else {
            $data = @yaml_parse_file($file, 0);
        }

        if ($data === false) {
            $error = error_get_last();
            throw new ParseException($error);
        }

        parent::__construct((array) $data);
    }
}
