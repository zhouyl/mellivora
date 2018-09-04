<?php

namespace Mellivora\Application;

use BadMethodCallException;
use Mellivora\Http\Request;
use Mellivora\Http\Response;
use Mellivora\Support\Arr;
use Mellivora\Support\Str;
use Slim\Exception\NotFoundException;

/**
 * MVC 模式下的 controller 基类
 */
class Controller
{
    /**
     * @var \Mellivora\Application\Container
     */
    protected $container;

    /**
     * @var \Mellivora\Http\Request
     */
    protected $request;

    /**
     * @var \Mellivora\Http\Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * 构造方法
     *
     * @param \Mellivora\Application\Container $container
     * @param \Mellivora\Http\Request          $request
     * @param \Mellivora\Http\Response         $response
     * @param array                            $arguments
     */
    public function __construct(
        Container $container,
        Request $request,
        Response $response,
        array $arguments
    ) {
        $this->container = $container;
        $this->request   = $request;
        $this->response  = $response;
        $this->arguments = $request->getAttribute('route')->getArguments();
    }

    /**
     * 获取当前路由参数
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getArgument($name, $default = null)
    {
        return Arr::get($this->arguments, $name, $default);
    }

    /**
     * 设置当前路由参数，该参数仅在当前控制器内生效
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return \Mellivora\Application\Controller
     */
    public function setArgument($name, $value)
    {
        $this->arguments[$name] = $value;

        return $this;
    }

    /**
     * 获取所有的路由参数
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * 当调用不存在的方法时，会调用该方法
     *
     * @param string $method
     * @param array  $args
     *
     * @throws \BadMethodCallException|\Slim\Exception\NotFoundException
     */
    public function __call($method, array $args)
    {
        // 重定向到 notFound 的 action
        if (method_exists($this, 'notFoundAction')) {
            return $this->notFoundAction(...$args);
        }

        // action 不存在，返回 http not found
        if (Str::endsWith($method, 'Action')) {
            throw new NotFoundException($this->request, $this->response);
        }

        throw new BadMethodCallException("Method {$method} does not exist.");
    }
}
