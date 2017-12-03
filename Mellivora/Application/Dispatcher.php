<?php

namespace Mellivora\Application;

use Mellivora\Http\Request;
use Mellivora\Http\Response;
use Mellivora\Support\Arr;
use Mellivora\Support\Str;
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
     * 检测 controller 的 class 类型
     *
     * @param  array                                                         $args
     * @throws \Slim\Exception\NotFoundException|\UnexpectedValueException
     * @return string
     */
    protected function detectControllerClass(array $args)
    {
        $class = str_replace('\\\\', '\\', sprintf(
            '\%s\%s\%sController',
            $args['namespace'],
            Str::studly($args['module']),
            Str::studly($args['controller'])
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
     * 执行 route 请求，分发到 controller/action 执行
     *
     * @param  \Mellivora\Http\Request  $request
     * @param  \Mellivora\Http\Response $response
     * @param  array                    $args
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        // 检测，并实例化 controller
        $class   = $this->detectControllerClass($args);
        $handler = new $class($this->container, $request, $response, $args);

        try {
            /**
             * 调用初始化方法
             *
             * 当返回 false 时，中断 action 执行并返回 $response
             * 当返回 Response 时，中断 action 执行并返回其结果
             */
            if (method_exists($handler, 'initialize')) {
                $initialize = $handler->initialize();

                if ($initialize === false) {
                    return $response;
                }

                if ($initialize instanceof Response) {
                    return $initialize;
                }
            }

            $method = $args['action'] . 'Action';

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
            /**
             * 当 controller 中存在 exceptionHandler 方法时
             * 调用该方法来对异常进行统一处理
             */
            if (method_exists($handler, 'exceptionHandler')) {
                $return = $handler->exceptionHandler($e);
            } else {
                throw $e;
            }
        }

        /**
         * 根据 return 的结果进行 response 格式化处理
         */
        if (is_array($return)) {
            $response = $response->withJson($return);
        } elseif (!$return instanceof Response) {
            $response = $response->write((string) $return);
        } else {
            $response = $return;
        }

        /**
         * 当 controller 中存在 finalize 方法时
         * 调用该方法，对响应结果进行再处理
         * 如果该方法 return 返回一个 response 的结果
         * 则使用 response 做为最终响应结果
         */
        if (method_exists($handler, 'finalize')) {
            $finalize = $handler->finalize($response);
            if ($finalize instanceof Response) {
                return $finalize;
            }
        }

        return $response;
    }
}
