<?php

namespace Mellivora\Application;

use Mellivora\Support\Providers\ServiceProvider;
use Slim\Container as SlimContainer;
use UnexpectedValueException;

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
                if (is_subclass_of($class, ServiceProvider::class)) {
                    (new $class($this))->register();
                } else {
                    throw new UnexpectedValueException(
                        'Provider must return instance of ' . ServiceProvider::class);
                }
            }
        }
    }

    /**
     * 调用 Container 的 offsetSet 方法
     *
     * @see   Pimple\Container::offsetSet
     *
     * @param string $id
     * @param mixed  $value
     */
    public function set($id, $value)
    {
        return parent::offsetSet($id, $value);
    }

    /**
     * 调用 Container 的 offsetUnset 方法
     *
     * @see   Pimple\Container::offsetUnset
     *
     * @param string $id
     * @param mixed  $value
     */
    public function remove($id)
    {
        return parent::offsetUnset($id);
    }

    /********************************************************************************
     * 魔术方法补充，__get/__isset 已经在 Slim\Container 中实现
     *******************************************************************************/

    public function __set($id, $value)
    {
        return parent::offsetSet($id, $value);
    }

    public function __unset($id)
    {
        return parent::offsetUnset($id);
    }
}
