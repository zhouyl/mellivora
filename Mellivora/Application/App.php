<?php

namespace Mellivora\Application;

use Mellivora\Support\Facades\Facade;
use Mellivora\Support\ServiceProvider;
use Mellivora\Support\Traits\Singleton;
use Slim\App as SlimApp;
use UnexpectedValueException;

/**
 * 定义 json_encode 的默认选项
 */
if (!defined('JSON_ENCODE_OPTION')) {
    define('JSON_ENCODE_OPTION', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * 重写 Slim\App 类
 *
 * 对 facades/provider 进行扩展
 *
 * @see \Slim\App
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
        // 构造 container
        if (is_array($container)) {
            $container = new Container($container);
        }

        parent::__construct($container);
        Facade::setFacadeApplication($this);

        $this->registerSingleton();
        $this->registerFacades();
        $this->registerProviders();
        $this->registerRouteArguments();
    }

    /**
     * 注册类别名
     */
    protected function registerFacades()
    {
        $container = $this->getContainer();

        if ($container->has('facades')) {
            foreach ($container->get('facades') as $alias => $abstract) {
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
     * 注册默认路由参数
     */
    protected function registerRouteArguments()
    {
        $this->add(function ($request, $response, $next) {
            $request->getAttribute('route')
                ->setArguments([
                    'namespace'  => 'App\Controllers',
                    'module'     => '',
                    'controller' => 'index',
                    'action'     => 'index',
                ]);

            return $next($request, $response);
        });
    }

    /**
     * 获取当前系统环境
     *
     * @return string
     */
    public function environment()
    {
        return $this->getContainer()['settings']['environment'];
    }
}
