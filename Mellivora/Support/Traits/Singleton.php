<?php

namespace Mellivora\Support\Traits;

trait Singleton
{

    /**
     * 当前使用的单例实例
     *
     * @var object
     */
    protected static $instance;

    /**
     * 允许单例模式调用
     *
     * @return object
     */
    public static function instance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * 将当前实例注册为单例
     *
     * @return object
     */
    public function registerSingleton()
    {
        self::$instance = $this;

        return $this;
    }
}
