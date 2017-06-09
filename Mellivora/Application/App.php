<?php

namespace Mellivora\Application;

use Mellivora\Facades\Facade;
use Slim\App as SlimApp;

/**
 * 重写 Slim\App 类
 *
 * 对 facades 进行扩展
 */
class App extends SlimApp
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
}
