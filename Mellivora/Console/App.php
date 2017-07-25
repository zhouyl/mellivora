<?php

namespace Mellivora\Console;

use InvalidArgumentException;
use Mellivora\Application\Container;
use Mellivora\Console\Command;
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
     * @var \Mellivora\Application\Container
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function __construct($container = [], $name = 'Mellivora Console Tools', $version = 'UNKNOWN')
    {
        if (is_array($container)) {
            $container = new Container($container);
        }

        if (!$container instanceof ContainerInterface) {
            throw new InvalidArgumentException('Expected a ContainerInterface');
        }

        $this->container = $container;

        parent::__construct($name, $version);

        // Facade 初始化设
        Facade::setFacadeApplication($this);
        $this->registerSingleton();
        $this->registerFacades();
        $this->registerProviders();
        $this->registerUserCommands();
        $this->registerDefaultCommands();
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
        if (isset($this->container['facades'])) {
            foreach ($this->container['facades'] as $alias => $abstract) {
                class_alias($abstract, $alias);
            }
        }
    }

    /**
     * 注册 Service Providers
     */
    protected function registerProviders()
    {
        if (isset($this->container['providers'])) {
            foreach ($this->container['providers'] as $class) {
                if (!is_subclass_of($class, ServiceProvider::class)) {
                    throw new UnexpectedValueException(
                        $class . ' must return instance of ' . ServiceProvider::class);
                }

                (new $class($this))->register();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register($name)
    {
        return $this->add((new Command($his->getContainer()))->setName($name));
    }

    /**
     * {@inheritdoc}
     */
    public function addCommands(array $commands)
    {
        foreach ($commands as $command) {
            if (is_string($command) && is_subclass_of($command, Command::class)) {
                $command = new $command($this->getContainer());
            }

            $this->add($command);
        }
    }

    /**
     * 注册用户定义的 Commands
     */
    protected function registerUserCommands()
    {
        if (isset($this->container['commands'])) {
            $this->addCommands($this->container['commands']);
        }
    }

    /**
     * 注册默认的 Commands
     */
    protected function registerDefaultCommands()
    {
        $this->addCommands([
            \Mellivora\Console\Commands\ViewClearCommand::class,
            \Mellivora\Console\Commands\TestMakeCommand::class,
            \Mellivora\Console\Commands\ConsoleMakeCommand::class,
            \Mellivora\Console\Commands\ProviderMakeCommand::class,
            \Mellivora\Console\Commands\MiddlewareMakeCommand::class,
            \Mellivora\Console\Commands\ControllerMakeCommand::class,
            \Mellivora\Console\Commands\ModelMakeCommand::class,
            \Mellivora\Database\Console\Seeds\SeedCommand::class,
            \Mellivora\Database\Console\Seeds\SeederMakeCommand::class,
            \Mellivora\Database\Console\Migrations\InstallCommand::class,
            \Mellivora\Database\Console\Migrations\MigrateCommand::class,
            \Mellivora\Database\Console\Migrations\MigrateMakeCommand::class,
            \Mellivora\Database\Console\Migrations\ResetCommand::class,
            \Mellivora\Database\Console\Migrations\RollbackCommand::class,
            \Mellivora\Database\Console\Migrations\RefreshCommand::class,
            \Mellivora\Database\Console\Migrations\StatusCommand::class,
        ]);
    }

    /**
     * 获取当前系统环境
     *
     * @return string
     */
    public function environment()
    {
        return $this->container['settings']['environment'];
    }
}
