<?php

namespace Mellivora\Application;

use Mellivora\Support\Providers\ServiceProvider;
use Mellivora\Support\Str;
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
                if (!is_subclass_of($class, ServiceProvider::class)) {
                    throw new UnexpectedValueException(
                        $class . ' must return instance of ' . ServiceProvider::class);
                }

                (new $class($this))->register();
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
        return $this->offsetSet($id, $value);
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
        return $this->offsetUnset($id);
    }

    /**
     * 将驼峰式的 id 转变为以 . 为连接格式的 id
     *
     * 使得 $container->viewFinder 相当于 $container->get('view.finder')
     *
     * @param  string   $id
     * @return string
     */
    protected function sanitizeId($id)
    {
        return Str::snake($id, '.');
    }

    /********************************************************************
     * 魔术方法重写或补充
     ********************************************************************/

    public function __set($id, $value)
    {
        return $this->offsetSet($this->sanitizeId($id), $value);
    }

    public function __unset($id)
    {
        return $this->offsetUnset($this->sanitizeId($id));
    }

    public function __get($id)
    {
        return $this->get($this->sanitizeId($id));
    }

    public function __isset($id)
    {
        return $this->has($this->sanitizeId($id));
    }
}
