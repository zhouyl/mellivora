<?php

namespace Mellivora;

use Mellivora\Config\Ini;
use Mellivora\Config\Json;
use Mellivora\Config\Php;
use Mellivora\Config\Xml;
use Mellivora\Config\Yaml;

/**
 * 配置文件处理类
 *
 * 可根据配置的路径，以及指定的解释器顺序，自动查找并加载配置数据
 *
 * <code>
 * // 设定加载规则
 * Config::setup([
 *     'paths'   => [
 *         dirname(__DIR__) . '/config',
 *         dirname(__DIR__) . '/config/production',
 *     ],
 *     'parsers' => [
 *         'php'  => Mellivora\Config\Php::class,
 *         'yaml' => Mellivora\Config\Yaml::class,
 *         'ini'  => Mellivora\Config\Ini::class,
 *         'json' => Mellivora\Config\Json::class,
 *         'xml'  => Mellivora\Config\xml::class,
 *     ],
 * ]);
 *
 *  // 加载配置文件
 *  var_dump(Config::load('db'));
 *
 *  // 加载配置数据
 *  var_dump(Config::get('db.default.host'));
 * </code>
 */
class Config
{
    /**
     * 默认的配置文件的查找路径，查找顺序从最后注册的路径开始（数组的底部）
     *
     * @var array
     */
    protected static $paths = [];

    /**
     * 配置文件将按照扩展名的先后顺序查找并载入
     *
     * @var array
     */
    protected static $parsers = [
        'php'  => Php::class,
        'yaml' => Yaml::class,
        'ini'  => Ini::class,
        'json' => Json::class,
        'xml'  => Xml::class,
    ];

    /**
     * 配置文件自动加载参数配置
     *
     * 可支持的配置选项包括 paths/parsers
     *
     * @param array $options
     */
    public static function setup(array $options)
    {
        foreach ($options as $method => $value) {
            method_exists(self::class, $method) && self::$method($value);
        }
    }

    /**
     * 新增配置查找路径，最后增加的路径会被优先查找
     *
     * @param array $paths
     */
    public static function paths(array $paths)
    {
        foreach ($paths as $path) {
            array_push(self::$paths, $path);
        }
    }

    /**
     * 设定配置文件解释器
     *
     * @param array $parsers
     */
    public static function parsers(array $parsers)
    {
        self::$parsers = $parsers;
    }

    /**
     * 根据名称，自动查找并载入配置
     *
     * <code>
     * Mellivora\Config::load('db');
     * </code>
     *
     * @param  string         $name
     * @return Object|false
     */
    public static function load($name)
    {
        foreach (array_reverse(self::$paths) as $path) {
            foreach (self::$parsers as $ext => $parser) {
                $file = "$path/$name.$ext";
                if (is_file($file)) {
                    return new $parser($file);
                }
            }
        }

        return false;
    }

    /**
     * 根据配置名称及路径，加载配置数据
     *
     * <code>
     * Mellivora\Config::get('db.default.host');
     * </code>
     *
     * @param  string  $namePath
     * @param  mixed   $default
     * @return mixed
     */
    public static function get($namePath, $default = null)
    {
        $parts  = explode('.', $namePath);
        $config = self::load(array_shift($parts));

        if ($config === false) {
            return $default;
        }

        if (empty($parts)) {
            return $config;
        }

        return $config->get(implode('.', $parts), $default);
    }
}
