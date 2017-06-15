<?php

namespace Mellivora\Application;

use Mellivora\Support\Facades\Facade;
use Mellivora\Support\Traits\Singleton;
use Slim\App as SlimApp;

/**
 * 重写 Slim\App 类
 *
 * 对 facades 进行扩展
 */
class App extends SlimApp
{
    /**
     * 使用 Singleton，让 App 支持单例调用
     */
    use Singleton;

    /**
     * {@inheritdoc}
     */
    public function __construct($container = [])
    {
        // 将 app 注册为允许单例调用
        $this->registerSingleton();

        // Facade 初始化设置
        Facade::setFacadeApplication($this);

        // 构造 container
        if (is_array($container)) {
            $container = new Container($container);
        }

        parent::__construct($container);
    }

    /**
     * {@inheritdoc}
     */
    public function run($silent = false)
    {
        return parent::run($silent);
    }
}
