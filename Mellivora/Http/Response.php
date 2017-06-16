<?php

namespace Mellivora\Http;

use Mellivora\Application\App;
use Slim\Http\Headers;
use Slim\Http\Response as SlimHttpResponse;

/**
 * 继承 Slim\Http\Response 并对其进行扩展
 */
class Response extends SlimHttpResponse
{

    /**
     * 快速创建一个 Response 实例
     *
     * @return Mellivora\Http\Response
     */
    public static function newInstance()
    {
        $container = App::getInstance()->getContainer();
        $response  = new self(200, new Headers(['Content-Type' => 'text/html; charset=UTF-8']));

        return $response->withProtocolVersion($container->get('settings')['httpVersion']);
    }

    /**
     * 指定 headers 头部信息
     *
     * @param  array    $headers
     * @return static
     */
    public function withHeaders(array $headers)
    {
        $clone = clone $this;

        foreach ($headers as $name => $value) {
            $clone->headers->set($name, $value);
        }

        return $clone;
    }

    /**
     * Create a new redirect response.
     *
     * @param  string|UriInterface $url
     * @param  int|null            $status
     * @return static
     */
    public function redirect($url, $status = null)
    {
        return $this->withRedirect($url, $status);
    }
}
