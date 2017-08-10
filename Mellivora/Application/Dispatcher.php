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
     * @var \Mellivora\Application\Container
     */
    protected $container;

    /**
     * @param \Mellivora\Application\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * 刷新 container 中注册的 request/response 组件
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     */
    protected function refreshContainer(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {
        // 需要在 container 中删除 request/response
        unset($this->container['request'], $this->container['response']);

        // 重新指定 request/response
        $this->container['request']  = $request;
        $this->container['response'] = $response;
    }

    /**
     * 检测 controller 的 class 类型
     *
     * @param  array                                                         $parameters
     * @throws \Slim\Exception\NotFoundException|\UnexpectedValueException
     * @return string
     */
    protected function detectControllerClass(array $parameters)
    {
        $class = str_replace('\\\\', '\\', sprintf(
            '%s\\%s\\%sController',
            $parameters['namespace'],
            $parameters['module'],
            $parameters['controller']
        ));

        if (!class_exists($class)) {
            throw new NotFoundException(
                $this->container['request'], $this->container['response']);
        }

        // controller 类型检测
        if (!is_subclass_of($class, Controller::class)) {
            throw new UnexpectedValueException(
                $class . ' must return instance of ' . Controller::class);
        }

        return $class;
    }

    /**
     * Invoke a route callable with request, response, and all route parameters
     * as an array of arguments.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request
     * @param  \Psr\Http\Message\ResponseInterface      $response
     * @param  array                                    $args
     * @return mixed
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $this->refreshContainer($request, $response);

        // 格式化获取到的参数
        $parameters = [
            'namespace'  => Arr::get($args, 'namespace', '\\App\\Controllers'),
            'module'     => Str::studly(Arr::get($args, 'module', '')),
            'controller' => Str::studly(Arr::get($args, 'controller', 'index')),
            'action'     => Str::camel(Arr::get($args, 'action', 'index')),
        ] + $args;

        // 检测，并实例化 controller
        $class   = $this->detectControllerClass($parameters);
        $handler = new $class($this->container, $parameters);

        try {
            // 调用初始化方法，当初始化为 false 时，中断 action 的执行，并返回 response 实例
            if (method_exists($handler, 'initialize') && $handler->initialize() === false) {
                return $response;
            }

            $method = $parameters['action'] . 'Action';

            // 移除不需要用到的值，以便 action 调用
            unset(
                $args['namespace'],
                $args['module'],
                $args['controller'],
                $args['action']
            );

            // call action
            $return = $handler->$method(...array_values($args));
        } catch (\Exception $e) {
            // 当 controller 中存在 exceptionHandler 方法时
            // 调用该方法来对异常进行统一处理
            if (method_exists($handler, 'exceptionHandler')) {
                $return = $handler->exceptionHandler($e);
            } else {
                throw $e;
            }
        } finally {
            if (method_exists($handler, 'finalize')) {
                $handler->finalize();
            }
        }

        if (is_array($return)) {
            return $response->withJson($return);
        }

        if (!$return instanceof ResponseInterface) {
            return $response->write((string) $return);
        }

        return $return;
    }
}
