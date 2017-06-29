<?php

namespace Mellivora\Application;

use Mellivora\Support\Facades\Facade;
use Mellivora\Support\Middlewares\Middleware;
use Mellivora\Support\Providers\ServiceProvider;
use Mellivora\Support\Traits\Singleton;
use Slim\App as SlimApp;
use UnexpectedValueException;

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

        $this->registerAliases();
        $this->registerProviders();
        $this->registerMiddlewares();
    }

    /**
     * 注册类别名
     */
    protected function registerAliases()
    {
        $container = $this->getContainer();

        if ($container->has('aliases')) {
            foreach ($container->get('aliases') as $alias => $abstract) {
                class_alias($abstract, $alias);
            }
        }
    }

    /**
     * 注册 Service Providers
     */
    protected function registerProviders()
    {
        $container = $this->getContainer();

        if ($container->has('providers')) {
            foreach ($container->get('providers') as $class) {
                if (!is_subclass_of($class, ServiceProvider::class)) {
                    throw new UnexpectedValueException(
                        $class . ' must return instance of ' . ServiceProvider::class);
                }

                (new $class($this))->register();
            }
        }
    }

    /**
     * 注册 Middlewares
     */
    protected function registerMiddlewares()
    {
        $container = $this->getContainer();

        if ($container->has('middlewares')) {
            foreach ($container->get('middlewares') as $class) {
                if (!is_subclass_of($class, Middleware::class)) {
                    throw new UnexpectedValueException(
                        $class . ' must return instance of ' . Middleware::class);
                }

                (new $class($this))->register();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function run($silent = false)
    {
        return parent::run($silent);
    }
}
