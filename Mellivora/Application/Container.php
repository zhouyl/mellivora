<?php

namespace Mellivora\Application;

use Mellivora\Support\Str;
use ReflectionClass;
use RuntimeException;
use Slim\Container as SlimContainer;

/**
 * 重写 Slim\Container 容器类
 */
class Container extends SlimContainer
{

    /**
     * @var array
     */
    protected $aliases = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);
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
     * 为兼容 laravel 组件而编写的方法
     *
     * @param  string    $abstract
     * @return boolean
     */
    public function bound($abstract)
    {
        return isset($this->aliases[$abstract]);
    }

    /**
     * 为兼容 laravel 组件而编写的方法
     *
     * @param  string             $abstract
     * @param  array              $parameters
     * @throws RuntimeException
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
        if ($this->offsetExists($abstract)) {
            return $this->offsetGet($abstract);
        }

        if (!class_exists($abstract)) {
            throw new RuntimeException("Can not make an undefined class [$abstract]");
        }

        $reflector = new ReflectionClass($abstract);

        // 检测是否可实例化
        if (!$reflector->isInstantiable()) {
            throw new RuntimeException("The class [$abstract] is not instantiable.");
        }

        $instance = $reflector->newInstanceArgs($parameters);

        $this->offsetSet($abstract, $instance);

        return $instance;
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

    /**
     * 将 class name 转换为 id
     *
     * @param  string   $id
     * @return string
     */
    protected function getAbstractId($id)
    {
        if (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }

        return $id;
    }

    /********************************************************************
     * 魔术方法重写或补充
     ********************************************************************/

    public function offsetSet($id, $value)
    {
        parent::offsetSet($id, $value);

        if (is_object($id)) {
            $this->aliases[get_class($id)] = $id;
        } else {
            // 重新注册时，需要删除旧的别名依赖
            unset($this->aliases[$id]);
        }
    }

    public function offsetGet($id)
    {
        $value = parent::offsetGet($this->getAbstractId($id));

        // 当实例被获取成功时，注册到别名列表中
        if (!isset($this->aliases[$id]) && is_object($value)) {
            $this->aliases[get_class($value)] = $id;
        }

        return $value;
    }

    public function offsetExists($id)
    {
        return parent::offsetExists($this->getAbstractId($id));
    }

    public function offsetUnset($id)
    {
        return parent::offsetUnset($this->getAbstractId($id));
    }

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
