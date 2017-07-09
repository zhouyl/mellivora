<?php

namespace Mellivora\Console;

use InvalidArgumentException;
use Mellivora\Application\Container;
use Mellivora\Support\Facades\Facade;
use Mellivora\Support\ServiceProvider;
use Mellivora\Support\Traits\Singleton;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use UnexpectedValueException;

/**
 * 定义 json_encode 的默认选项
 */
if (!defined('JSON_ENCODE_OPTION')) {
    define('JSON_ENCODE_OPTION', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

class App extends Application
{

    /**
     * 使用 Singleton，让 App 支持单例调用
     */
    use Singleton;

    /**
     * Current version
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * @var Mellivora\Application\Container
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function __construct($container = [], $name = 'Mellivora Framework', $version = self::VERSION)
    {
        // 将 app 注册为允许单例调用
        $this->registerSingleton();

        // Facade 初始化设置
        Facade::setFacadeApplication($this);

        // 构造 container
        if (is_array($container)) {
            $container = new Container($container);
        }

        if (!$container instanceof ContainerInterface) {
            throw new InvalidArgumentException('Expected a ContainerInterface');
        }

        $this->container = $container;

        $this->registerFacades();
        $this->registerProviders();

        parent::__construct($name, $version);
    }

    /**
     * 获取 container 实例
     *
     * @return \Mellivora\Application\Container
     */
    public function getContainer()
    {
        return $this->container;
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
}
