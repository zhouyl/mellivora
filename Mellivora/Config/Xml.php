<?php

namespace Mellivora\Config;

/**
 * xml 格式配置文件解释器
 */
class Xml extends NativeArray
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
        libxml_use_internal_errors(true);
        $data = simplexml_load_file($file, null, LIBXML_NOERROR);

        if ($data === false) {
            $errors    = libxml_get_errors();
            $lastError = array_pop($errors);
            $error     = [
                'message' => $lastError->message,
                'type'    => $lastError->level,
                'code'    => $lastError->code,
                'file'    => $lastError->file,
                'line'    => $lastError->line,
            ];

            throw new ParseException($error);
        }

        parent::__construct(json_decode(json_encode($data), true));
    }
}
