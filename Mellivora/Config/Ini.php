<?php

namespace Mellivora\Config;

/**
 * ini 格式配置文件解释器
 */
class Ini extends NativeArray
{
    /**
     * 构造方法
     *
     * @param string $file
     * @param int    $mode INI_SCANNER_NORMAL|INI_SCANNER_RAW
     *
     * @throws \Mellivora\Config\ParseException
     */
    public function __construct($file, $mode = INI_SCANNER_RAW)
    {
        $data = @parse_ini_file($file, true, $mode);

        if ($data === false) {
            $error = error_get_last();

            throw new ParseException($error);
        }

        $config = [];
        foreach ($data as $section => $directives) {
            if (is_array($directives)) {
                $sections = [];

                foreach ($directives as $path => $lastValue) {
                    $sections[] = $this->parseIniString((string) $path, $lastValue);
                }

                if (count($sections)) {
                    $config[$section] = call_user_func_array('array_merge_recursive', $sections);
                }
            } else {
                $config[$section] = $this->cast($directives);
            }
        }

        parent::__construct($config);
    }

    /**
     * 将 ini 的 . 分隔符构建的数据，解析为多维数组
     *
     * <code>
     * $this->parseIniString("path.hello.world", "value for last key");
     *
     * // result
     * [
     *      "path" => [
     *          "hello" => [
     *              "world" => "value for last key",
     *          ],
     *      ],
     * ];
     * </code>
     *
     * @param mixed $path
     * @param mixed $value
     */
    protected function parseIniString($path, $value)
    {
        $value = $this->cast($value);
        $pos   = strpos($path, '.');

        if ($pos === false) {
            return [$path => $value];
        }

        $key  = substr($path, 0, $pos);
        $path = substr($path, $pos + 1);

        return [$key => $this->parseIniString($path, $value)];
    }

    /**
     * php 对 ini 的解释有不到位的地方，部分值转换需要手动处理
     *
     * @param mixed $ini
     *
     * @return mixed
     */
    protected function cast($ini)
    {
        if (is_array($ini)) {
            foreach ($ini as $key => $val) {
                $ini[$key] = $this->cast($val);
            }
        }

        if (is_string($ini)) {
            if (in_array(strtolower($ini), ['true', 'yes', 'on'])) {
                return true;
            }
            if (in_array(strtolower($ini), ['false', 'no', 'off'])) {
                return false;
            }
            if (in_array(strtolower($ini), ['', 'null'])) {
                return null;
            }
            if (is_numeric($ini)) {
                if (preg_match('/[.]+/', $ini)) {
                    return (float) $ini;
                }

                return (int) $ini;
            }
        }

        return $ini;
    }
}
