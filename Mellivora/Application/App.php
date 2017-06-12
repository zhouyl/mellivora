<?php

namespace Mellivora\Application;

use ArrayAccess;
use Mellivora\Support\Facades\Facade;
use Slim\App as SlimApp;

/**
 * 环境常量定义
 */
defined('PRODUCTION') || define('PRODUCTION', false);
defined('STAGING') || define('STAGING', false);
defined('TESTING') || define('TESTING', false);
defined('DEVELOPMENT') || define('DEVELOPMENT', !(PRODUCTION || STAGING || TESTING));

/**
 * 重写 Slim\App 类
 *
 * 对 facades 进行扩展
 */
class App extends SlimApp implements ArrayAccess
{
    /**
     * {@inheritdoc}
     */
    public function __construct($container = [])
    {
        if (is_array($container)) {
            $container = new Container($container);
        }

        parent::__construct($container);

        Facade::setFacadeApplication($this);
    }

    /**
     * {@inheritdoc}
     */
    public function run($silent = false)
    {
        return parent::run($silent);
    }

    /**
     * 判断当前项目环境，或者获取当前项目环境
     *
     * @param  string           $asset
     * @return boolean|string
     */
    public function env($asset = null)
    {
        if (PRODUCTION) {
            $env = 'production';
        } elseif (STAGING) {
            $env = 'staging';
        } elseif (TESTING) {
            $env = 'testing';
        } else {
            $env = 'development';
        }

        return $asset ? strtolower($asset) === $env : $env;
    }

    /**
     * 判断当前是否生产环境
     *
     * @return boolean
     */
    public function inProduction()
    {
        return $this->env('production');
    }

    /**
     * @see Slim\Container::get
     *
     * @param  string  $id
     * @return mixed
     */
    public function make($id)
    {
        return $this->getContainer()->get($id);
    }

    /********************************************************************************
     * 补充对 Container 的 ArrayAccess 调用
     *******************************************************************************/

    public function offsetSet($id, $value)
    {

        return $this->getContainer()->offsetSet($id, $value);
    }

    public function offsetGet($id)
    {

        return $this->getContainer()->get($id);
    }

    public function offsetExists($id)
    {

        return $this->getContainer()->offsetExists($id);
    }

    public function offsetUnset($id)
    {
        return $this->getContainer()->offsetUnset($id);
    }

    /********************************************************************************
     * 魔术方法，补充对 Container 的调用
     *******************************************************************************/

    public function __get($id)
    {
        return $this->getContainer()->get($id);
    }

    public function __set($id, $value)
    {
        return $this->getContainer()->offsetSet($id, $value);
    }

    public function __isset($id)
    {
        return $this->getContainer()->offsetExists($id);
    }

    public function __unset($id)
    {
        return $this->getContainer()->offsetUnset($id);
    }
}
