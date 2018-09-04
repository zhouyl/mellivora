<?php

namespace Mellivora\Config;

/**
 * json 格式配置文件解释器
 */
class Json extends NativeArray
{
    /**
     * 构造方法
     *
     * @param string $file
     *
     * @throws \Mellivora\Config\ParseException
     */
    public function __construct($file)
    {
        $data = json_decode(file_get_contents($file), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error_message = 'Syntax error';
            if (function_exists('json_last_error_msg')) {
                $error_message = json_last_error_msg();
            }

            throw new ParseException([
                'message' => $error_message,
                'type'    => json_last_error(),
                'file'    => $file,
            ]);
        }

        parent::__construct($data);
    }
}
