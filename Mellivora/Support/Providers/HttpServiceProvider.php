<?php

namespace Mellivora\Support\Providers;

use Mellivora\Http\Cookies;
use Mellivora\Http\Request;
use Mellivora\Http\Response;
use Mellivora\Support\Providers\ServiceProvider;
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
        $this->registerCookies();
        $this->registerRequest();
        $this->registerResponse();
    }

    /**
     * Cookie 处理
     */
    public function registerCookies()
    {
        $this->container['cookies'] = function ($container) {
            $config = $container['config']->get('session.cookies');

            $cookies = new Cookies($config->toArray());

            $cookies->setEncryption($container['encryption']);

            return $cookies;
        };
    }

    /**
     * http request 请求处理类
     */
    public function registerRequest()
    {
        $this->container['request'] = function ($container) {
            return Request::createFromEnvironment($container['environment']);
        };
    }

    /**
     * http response 响应处理类
     */
    public function registerResponse()
    {
        $this->container['response'] = function ($container) {
            $headers  = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
            $response = new Response(200, $headers);

            return $response->withProtocolVersion($container['settings']['httpVersion']);
        };
    }

}
