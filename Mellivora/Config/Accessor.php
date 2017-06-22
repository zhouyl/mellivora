<?php

namespace Mellivora\Config;

use ArrayAccess;
use RuntimeException;

/**
 * 配置文件处理类
 *
 * 可根据配置的路径，以及指定的解释器顺序，自动查找并加载配置数据
 *
 * <code>
 * // 设定加载规则
 * $config = new Mellivora\Config\Accessor([
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
 *  var_dump($config->load('db'));
 *
 *  // 加载配置数据
 *  var_dump($config->get('db.default.host'));
 * </code>
 */
class Accessor implements ArrayAccess
{
    /**
     * 默认的配置文件的查找路径，查找顺序从最后注册的路径开始（数组的底部）
     *
     * @var array
     */
    protected $paths = [];

    /**
     * 缓存数据
     *
     * @var array
     */
    protected static $cached = [];

    /**
     * 配置文件将按照扩展名的先后顺序查找并载入
     *
     * @var array
     */
    protected $parsers = [
        'php'  => Php::class,
        'yaml' => Yaml::class,
        'ini'  => Ini::class,
        'json' => Json::class,
        'xml'  => Xml::class,
    ];

    /**
     * 构造方法
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setup($options);
    }

    /**
     * 配置文件自动加载参数配置
     *
     * 可支持的配置选项包括 paths/parsers
     *
     * @param array $options
     */
    public function setup(array $options)
    {
        foreach ($options as $method => $value) {
            $method = 'set' . ucfirst($method);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    /**
     * 设定配置查找路径
     *
     * @param  array                       $paths
     * @return Mellivora\Config\Accessor
     */
    public function setPaths(array $paths)
    {
        $this->paths = $paths;

        return $this;
    }

    /**
     * 新增配置查找路径，最后增加的路径会被优先查找
     *
     * @param  string                      $path
     * @return Mellivora\Config\Accessor
     */
    public function addPath($path)
    {
        array_push($this->paths, $path);

        return $this;
    }

    /**
     * 设定配置文件解释器
     *
     * @param array                   $parsers
     * @param Mellivora\Config\Parser $parser
     */
    public function setParsers(array $parsers)
    {
        $this->parsers = $parsers;

        return $this;
    }

    /**
     * 新增配置解释器
     *
     * @param string                  $ext
     * @param Mellivora\Config\Parser $parser
     */
    public function addParser($ext, Parser $parser)
    {
        $this->parsers[$ext] = $parser;

        return $this;
    }

    /**
     * 根据名称，自动查找并载入配置
     *
     * <code>
     * $config->load('db');
     * </code>
     *
     * @param  string         $name
     * @return Object|false
     */
    public function load($name)
    {
        if (!array_key_exists($name, self::$cached)) {
            foreach (array_reverse($this->paths) as $path) {
                foreach ($this->parsers as $ext => $parser) {
                    $file = "$path/$name.$ext";
                    if (is_file($file)) {
                        self::$cached[$name] = new $parser($file);
                        break 2;
                    }
                }
            }
        }

        return self::$cached[$name];
    }

    /**
     * 根据配置名称及路径，加载配置数据
     *
     * <code>
     * $config->get('db.default.host');
     * </code>
     *
     * @param  string  $path
     * @param  mixed   $default
     * @return mixed
     */
    public function get($path, $default = null)
    {
        $parts  = explode('.', $path);
        $config = $this->load(array_shift($parts));

        if ($config === false) {
            return $default;
        }

        if (empty($parts)) {
            return $config;
        }

        return $config->get(implode('.', $parts), $default);
    }

    /********************************************************************************
     * 通过对 ArrayAccess 的支持，使配置加载更自由
     *******************************************************************************/

    public function offsetGet($key)
    {
        return $this->get($key);
    }

    public function offsetSet($key, $value)
    {
        throw new RuntimeException('Can not set the config data');
    }

    public function offsetUnset($key)
    {
        throw new RuntimeException('Can not unset the config data');
    }

    public function offsetExists($key)
    {
        return $this->get($key) !== null;
    }
}
