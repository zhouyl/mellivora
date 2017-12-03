<?php

namespace Mellivora\Http;

use Mellivora\Support\ServiceProvider;
use Slim\Http\Headers;

class HttpServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCookiesProvider();
        $this->registerRequestProvider();
        $this->registerResponseProvider();
    }

    /**
     * 注册 Cookies 服务
     */
    public function registerCookiesProvider()
    {
        $this->container['cookies'] = function ($container) {
            $config = $container['config']->get('session.cookies');

            $cookies = new Cookies($config->toArray());

            // 数据需要加密处理
            if ($config->encrypt) {
                $cookies->setEncryption($container['crypt']);
            }

            return $cookies;
        };
    }

    /**
     * 注册 Http Request 服务
     */
    public function registerRequestProvider()
    {
        $this->container['request'] = function ($container) {
            $request = Request::createFromEnvironment($container['environment']);

            return $request;
        };
    }

    /**
     * 注册 Http Response 服务
     */
    public function registerResponseProvider()
    {
        $this->container['response'] = function ($container) {
            $headers  = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
            $response = new Response(200, $headers);

            return $response->withProtocolVersion($container['settings']['httpVersion']);
        };
    }
}
