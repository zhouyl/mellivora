<?php

namespace Mellivora\Application;

use Slim\Container as SlimContainer;

/**
 * 重写 Slim\Container 容器类
 *
 * 对 alias 及 provider 进行扩展
 */
class Container extends SlimContainer
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this->registerAliases();
        $this->registerProviders();
    }

    /**
     * 注册类别名
     */
    protected function registerAliases()
    {
        if ($this->has('aliases')) {
            foreach ($this->get('aliases') as $alias => $abstract) {
                class_alias($abstract, $alias);
            }
        }
    }

    /**
     * 注册 Service Providers
     */
    protected function registerProviders()
    {
        if ($this->has('providers')) {
            foreach ($this->get('providers') as $class) {
                (new $class())->register($this);
            }
        }
    }
}
