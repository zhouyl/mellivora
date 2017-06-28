<?php

namespace Mellivora\Application;

use BadMethodCallException;
use Mellivora\Support\Arr;
use Mellivora\Support\Str;
use Slim\Exception\NotFoundException;

/**
 * MVC 模式下的 controller 基类
 */
class Controller
{
    /**
     * @var Mellivora\Application\Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @param Mellivora\Application\Container $container
     */
    public function __construct(Container $container, array $parameters)
    {
        $this->container  = $container;
        $this->parameters = $parameters;

        if (method_exists($this, 'initialize')) {
            $this->initialize();
        }
    }

    /**
     * 获取 module 名称
     *
     * @return string
     */
    public function getModuleName()
    {
        return Arr::get($this->parameters, 'module');
    }

    /**
     * 获取 controller 名称
     *
     * @return string
     */
    public function getControllerName()
    {
        return Arr::get($this->parameters, 'controller');
    }

    /**
     * 获取 action 名称
     *
     * @return string
     */
    public function getActionName()
    {
        return Arr::get($this->parameters, 'action');
    }

    /**
     * 获取 Container 容器中注入的对象
     *
     * @param  string  $id
     * @return mixed
     */
    public function __get($id)
    {
        return $this->container->{$id};
    }

    /**
     * 当调用不存在的方法时，会调用该方法
     *
     * @param  string                                                    $method
     * @param  array                                                     $args
     * @throws Slim\Exception\NotFoundException|BadMethodCallException
     */
    public function __call($method, array $args)
    {
        // action 不存在，返回 http not found
        if (Str::endsWith($method, 'Action')) {
            throw new NotFoundException($this->container['request'], $this->container['response']);
        }

        throw new BadMethodCallException("Method {$method} does not exist.");
    }
}
