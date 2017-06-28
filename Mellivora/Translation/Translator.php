<?php

namespace Mellivora\Translation;

use DirectoryIterator;
use Mellivora\Support\Arr;

/**
 * 多语言翻译处理
 */
class Translator
{

    /**
     * 默认语言包
     *
     * @var string
     */
    protected $default = 'en-us';

    /**
     * 语言包加载路径
     *
     * @var array
     */
    protected $paths = [];

    /**
     * 语言包别名
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * 已加载的包名称
     *
     * @var array
     */
    protected $packages = [];

    /**
     * 已加载的语言包
     *
     * @var array
     */
    protected $imported = [];

    /**
     * Constructor
     *
     * @param string|array $paths
     */
    public function __construct($paths = [])
    {
        foreach (is_array($paths) ? $paths : [$paths] as $path) {
            $this->addBaseath($path);
        }
    }

    /**
     * 设定语言包加载路径
     *
     * @param  string                             $basepath
     * @return Mellivora\Translation\Translator
     */
    public function addBaseath($basepath)
    {
        foreach (new DirectoryIterator($basepath) as $dir) {
            if ($dir->isDir() && !$dir->isDot()) {
                $lang = $dir->getBasename();
                $path = $dir->getPathname();

                if (!isset($this->paths[$lang])) {
                    $this->paths[$lang] = [];
                }

                if (!in_array($path, $this->paths[$lang])) {
                    $this->paths[$lang] = $path;
                }

                // 新增加的目录，需要重新扫描已加载的语言包
                $this->loadPackages($lang, $path, [$lang] + $this->packages);
            }
        }

        return $this;
    }

    /**
     * 设定语言包别名
     *
     * @param  string                             $lang
     * @param  string|array                       $aliases
     * @return Mellivora\Translation\Translator
     */
    public function setAlias($lang, $aliases)
    {
        $lang = strtolower($lang);

        if (!is_array($aliases)) {
            $aliases = [$aliases];
        }
        $aliases = array_map('strtolower', $aliases);

        $this->aliases[$lang] = array_unique(
            array_merge($this->aliases[$lang] ?? [], $aliases));

        return $this;
    }

    /**
     * 设定默认的语言包
     *
     * @param  string                             $default
     * @return Mellivora\Translation\Translator
     */
    public function setDefault($default)
    {
        $this->default = $this->aliasToLang($default);

        return $this;
    }

    /**
     * 将别名转换为语言包名
     *
     * @param  string   $alias
     * @return string
     */
    public function aliasToLang($alias)
    {
        $alias = strtolower($alias);

        foreach ($this->aliases as $lang => $aliases) {
            if (in_array($alias, $aliases)) {
                return $lang;
            }
        }

        return $alias;
    }

    /**
     * 加载语言包
     *
     * @param  string|array                       $packages
     * @return Mellivora\Translation\Translator
     */
    public function import($packages)
    {
        if (!is_array($packages)) {
            $packages = func_get_args();
        }

        // 新增的语言包
        $diff = array_diff($packages, $this->packages);

        // 合并到已加载的语言包中
        $this->packages = array_merge($this->packages, $diff);

        // 载入新增的语言包
        foreach ($this->paths as $lang => $path) {
            $this->loadPackages($lang, $path, $diff);
        }

        return $this;
    }

    /**
     * 导出已加载的语言包数据
     *
     * @param  string  $lang
     * @return array
     */
    public function export($lang = null)
    {
        $lang = $lang ? $this->aliasToLang($lang) : $this->default;

        if (!isset($this->imported[$lang])) {
            return [];
        }

        return $this->imported[$lang];
    }

    /**
     * 执行翻译
     *
     * @param  string   $text
     * @param  array    $replace
     * @param  string   $lang
     * @return string
     */
    public function trans($text, array $replace = null, $lang = null)
    {
        $lang = $lang ? $this->aliasToLang($lang) : $this->default;

        if (isset($this->imported[$lang])) {
            $text = Arr::get($this->imported[$lang], $text, $text);
        }

        return $replace ? strtr($text, $replace) : $text;
    }

    /**
     * 反向翻译
     *
     * @param  string   $text
     * @param  string   $from
     * @param  string   $to
     * @return string
     */
    public function reverse($text, $from = null, $to = null)
    {
        $from = $from ? $this->aliasToLang($from) : $this->default;
        $to   = $to ? $this->aliasToLang($to) : null;

        if ($from === $to) {
            return $text;
        }

        if (isset($this->imported[$from])) {
            $text = Arr::get(array_flip($this->imported[$from]), $text, $text);
        }

        if ($to === null || $to = 'en-us') {
            return $text;
        }

        return $this->trans($text, null, $to);
    }

    /**
     * 加载语言包
     *
     * @param string $lang
     * @param string $path
     * @param array  $packages
     */
    protected function loadPackages($lang, $path, array $packages)
    {
        // 扫描目录并加载语言包
        foreach ($packages as $package) {
            $file = sprintf('%s/%s.php', $path, str_replace('.', '/', $package));

            // 语言文件不存在
            if (!is_file($file)) {
                continue;
            }

            // 无效语言包
            $return = include $file;
            if (!is_array($return)) {
                continue;
            }

            foreach ($return as $key => $value) {
                $this->imported[$lang][$key] = $value;
            }
        }
    }
}
