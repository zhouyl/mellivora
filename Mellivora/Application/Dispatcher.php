<?php

namespace Mellivora\Application;

use Mellivora\Support\Arr;
use Mellivora\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\NotFoundException;

/**
 * 一个通用的路由分发器
 *
 * 模拟实现了 mvc 模式下的 controller 分发工作
 */
class Dispatcher
{

    /**
     * @var Mellivora\Application\Container
     */
    protected $container;

    /**
     * @param Mellivora\Application\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Invoke a route callable with request, response, and all route parameters
     * as an array of arguments.
     *
     * @param  Psr\Http\Message\ServerRequestInterface $request
     * @param  Psr\Http\Message\ResponseInterface      $response
     * @param  array                                   $args
     * @return mixed
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        // 格式化获取到的 module & controller & action & namespace
        $module     = Str::studly(Arr::get($args, 'module', ''));
        $controller = Str::studly(Arr::get($args, 'controller', 'index'));
        $action     = Str::camel(Arr::get($args, 'action', 'index'));
        $namespace  = Arr::get($args, 'namespace', '\\App\\Controllers');

        // clone 复制一个 container
        $container = clone $this->container;

        // 需要在 container 中删除 request/response
        unset($container['request'], $container['response']);

        // 重新指定 request/response
        $container['request']  = $request;
        $container['response'] = $response;

        // 获取 controller class
        $class = str_replace('\\\\', '\\',
            "{$namespace}\\{$module}\\{$controller}Controller");

        // 获取 action method
        $method = "{$action}Action";

        // controller 检测
        if (!class_exists($class)) {
            throw new NotFoundException($request, $response);
        }

        // controller 类型检测
        if (!is_subclass_of($class, Controller::class)) {
            throw new UnexpectedValueException(
                $class . ' must return instance of ' . Controller::class);
        }

        // 实例化 controller
        $handler = new $class($container,
            array_merge($args, compact('module', 'controller', 'action')));

        // 去除不需要的值，预备 action 调用
        unset($args['module'], $args['controller'], $args['action']);

        // 执行
        return call_user_func_array([$handler, $method], $args);
    }
}
