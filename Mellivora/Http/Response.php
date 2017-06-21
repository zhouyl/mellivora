<?php

namespace Mellivora\Http;

use Slim\Http\Response as SlimHttpResponse;

/**
 * 继承 Slim\Http\Response 并对其进行扩展
 */
class Response extends SlimHttpResponse
{

    /**
     * 默认使用定义的全局 json 格式化选项输出
     *
     * {@inheritdoc}
     */
    public function withJson($data, $status = null, $encodingOptions = JSON_ENCODE_OPTION)
    {
        return parent::withJson($data, $status, $encodingOptions);
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
