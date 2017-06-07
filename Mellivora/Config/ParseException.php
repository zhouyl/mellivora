<?php

namespace Mellivora\Config;

/**
 * Mellivora\Config 异常处理
 *
 * 用于处理配置加载过程中的错误捕获
 */
class ParseException extends \ErrorException
{
    /**
     * @param array $error
     */
    public function __construct(array $error)
    {
        $message   = $error['message'] ?: 'There was an error parsing the file';
        $code      = $error['code'] ?? 0;
        $severity  = $error['type'] ?? 1;
        $filename  = $error['file'] ?? __FILE__;
        $lineno    = $error['line'] ?? __LINE__;
        $exception = $error['exception'] ?? null;

        parent::__construct($message, $code, $severity, $filename, $lineno, $exception);
    }
}
