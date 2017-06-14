<?php

namespace Mellivora\Http;

use Mellivora\Support\Str;
use Slim\Http\Request as SlimHttpRequest;

/**
 * 继续 Slim\Http\Request 并对其进行扩展
 */
class Request extends SlimHttpRequest
{

    /**
     * Get the request method.
     *
     * @return string
     */
    public function method()
    {
        return $this->getMethod();
    }

    /**
     * Get the root URL for the application.
     *
     * @return string
     */
    public function root()
    {
        return $this->getUri()->getBaseUrl();
    }

    /**
     * Get the URL (no query string) for the request.
     *
     * @return string
     */
    public function url()
    {
        return (string) $this->getUri()->withQuery('')->withFragment('');
    }

    /**
     * Get the full URL for the request.
     *
     * @return string
     */
    public function fullUrl()
    {
        return (string) $this->getUri();
    }

    /**
     * Get the full URL for the request with the added query string parameters.
     *
     * @param  array    $query
     * @return string
     */
    public function fullUrlWithQuery(array $query)
    {
        parse_str($this->getUri()->getQuery(), $queryParts);

        return (string) $this->getUri()
            ->withQuery(http_build_query(array_merge($queryParts, $query)));
    }

    /**
     * Get the current path info for the request.
     *
     * @return string
     */
    public function path()
    {
        $pattern = trim($this->getUri()->getPath(), '/');

        return $pattern == '' ? '/' : $pattern;
    }

    /**
     * Get the current encoded path info for the request.
     *
     * @return string
     */
    public function decodedPath()
    {
        return rawurldecode($this->path());
    }

    /**
     * Determine if the current request URI matches a pattern.
     *
     * @return bool
     */
    public function is()
    {
        foreach (func_get_args() as $pattern) {
            if (Str::is($pattern, $this->decodedPath())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the current request URL and query string matches a pattern.
     *
     * @return bool
     */
    public function fullUrlIs()
    {
        $url = $this->fullUrl();

        foreach (func_get_args() as $pattern) {
            if (Str::is($pattern, $url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the request is the result of an AJAX call.
     *
     * @return bool
     */
    public function ajax()
    {
        return $this->isXhr();
    }

    /**
     * Determine if the request is the result of an PJAX call.
     *
     * @return bool
     */
    public function pjax()
    {
        return $this->headers->get('X-PJAX') == true;
    }

    /**
     * Returns the client IP address.
     *
     * @return string
     */
    public function ip()
    {
        return $this->getClientAddress();
    }

    /**
     * Returns the client IP address.
     *
     * @return string
     */
    public function getClientAddress()
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // When a HTTP request is proxied, some proxy server will add requester's
            // IP address to $_SERVER['HTTP_X_FORWARDED_FOR'].
            // As a request may go through several proxies,
            // $_SERVER['HTTP_X_FORWARDED_FOR'] can contain several IP addresses separated with comma.
            foreach (explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']) as $address) {
                $address = trim($address);

                // Skip RFC 1918 IP's 10.0.0.0/8, 172.16.0.0/12 and 192.168.0.0/16
                if (!preg_match('/^(?:10|172\.(?:1[6-9]|2\d|3[01])|192\.168)\./', $address)) {
                    if (ip2long($address) != false) {
                        return $address;
                        break;
                    }
                }
            }
        }

        return $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'];
    }
}
