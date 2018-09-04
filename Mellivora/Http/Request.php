<?php

namespace Mellivora\Http;

use Mellivora\Support\Arr;
use Mellivora\Support\Str;
use Slim\Http\Request as SlimHttpRequest;
use Slim\Http\UploadedFile;

/**
 * 继承 Slim\Http\Request 并对其进行扩展
 */
class Request extends SlimHttpRequest
{
    /**
     * @var array
     */
    protected $languages;

    /**
     * 指定 headers 头部信息
     *
     * @param array $headers
     *
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
        return rtrim($this->getUri()->getBaseUrl(), '/');
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
        return $this->getUri();
    }

    /**
     * Get the full URL for the request with the added query string parameters.
     *
     * @param array $query
     *
     * @return string
     */
    public function fullUrlWithQuery(array $query)
    {
        parse_str($this->getUri()->getQuery(), $queryParts);

        return (string) $this->getUri()
            ->withQuery(http_build_query($query + $queryParts));
    }

    /**
     * Retrieve the host component of the URI.
     *
     * @return string the URI host
     */
    public function host()
    {
        return $this->getUri()->getHost();
    }

    /**
     * Get the current path info for the request.
     *
     * @return string
     */
    public function path()
    {
        $pattern = trim($this->getUri()->getPath(), '/');

        return $pattern === '' ? '/' : $pattern;
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
        return $this->headers->get('X-PJAX') === true;
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
                    if (ip2long($address) !== false) {
                        return $address;

                        break;
                    }
                }
            }
        }

        return Arr::get($_SERVER, 'HTTP_CLIENT_IP', $_SERVER['REMOTE_ADDR']);
    }

    /**
     * Retrieve post data.
     *
     * @return array
     */
    public function getPostParams()
    {
        if ($posts = $this->getParsedBody()) {
            return Arr::convert($posts, true);
        }

        return [];
    }

    /**
     * Fetch parameter value from post data.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getPostParam($key, $default = null)
    {
        return Arr::get($this->getPostParams(), $key, $default);
    }

    /**
     * Get all of the input and files for the request.
     *
     * @return array
     */
    public function all()
    {
        return array_replace_recursive($this->getParams(), $this->allFiles());
    }

    /**
     * Get an array of all of the files on the request.
     *
     * @return array
     */
    public function allFiles()
    {
        return $this->getUploadedFiles();
    }

    /**
     * Determine if the request contains a non-empty value for an input item.
     *
     * @param array|string $key
     *
     * @return bool
     */
    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $value) {
            if ($this->isEmptyString($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if the request contains a given input item key.
     *
     * @param array|string $key
     *
     * @return bool
     */
    public function exists($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        $input = $this->all();

        foreach ($keys as $value) {
            if (!Arr::has($input, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if a cookie is set on the request.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasCookie($key)
    {
        return !is_null($this->cookie($key));
    }

    /**
     * Determine if the uploaded data contains a file.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasFile($key)
    {
        if (!is_array($files = $this->file($key))) {
            $files = [$files];
        }

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a subset containing the provided keys with values from the input data.
     *
     * @param array|mixed $keys
     *
     * @return array
     */
    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = [];

        $input = $this->all();

        foreach ($keys as $key) {
            Arr::set($results, $key, data_get($input, $key));
        }

        return $results;
    }

    /**
     * Get all of the input except for a specified array of items.
     *
     * @param array|mixed $keys
     *
     * @return array
     */
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = $this->all();

        Arr::forget($results, $keys);

        return $results;
    }

    /**
     * Retrieve an input item from the request.
     *
     * @param string            $key
     * @param null|array|string $default
     *
     * @return array|string
     */
    public function input($key = null, $default = null)
    {
        return data_get($this->getParams(), $key, $default);
    }

    /**
     * Retrieve a query string item from the request.
     *
     * @param string            $key
     * @param null|array|string $default
     *
     * @return array|string
     */
    public function query($key = null, $default = null)
    {
        $queries = $this->getQueryParams();

        return is_null($key) ? $queries : data_get($queries, $key, $default);
    }

    /**
     * Retrieve a post data item from the request.
     *
     * @param string            $key
     * @param null|array|string $default
     *
     * @return array|string
     */
    public function post($key = null, $default = null)
    {
        $posts = $this->getPostParams();

        return is_null($key) ? $posts : data_get($posts, $key, $default);
    }

    /**
     * Retrieve a server variable from the request.
     *
     * @param string            $key
     * @param null|array|string $default
     *
     * @return array|string
     */
    public function server($key = null, $default = null)
    {
        $servers = $this->getServerParams();

        return is_null($key) ? $servers : data_get($servers, $key, $default);
    }

    /**
     * Retrieve a cookie from the request.
     *
     * @param string            $key
     * @param null|array|string $default
     *
     * @return array|string
     */
    public function cookie($key = null, $default = null)
    {
        $cookies = $this->getCookieParams();

        return is_null($key) ? $cookies : data_get($cookies, $key, $default);
    }

    /**
     * Retrieve a file from the request.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return null|array
     */
    public function file($key = null, $default = null)
    {
        $files = $this->getUploadedFiles();

        return is_null($key) ? $files : data_get($files, $key, $default);
    }

    /**
     * Intersect an array of items with the input data.
     *
     * @param array|mixed $keys
     *
     * @return array
     */
    public function intersect($keys)
    {
        return array_filter($this->only(is_array($keys) ? $keys : func_get_args()));
    }

    /**
     * Determine if the given input key is an empty string for "has".
     *
     * @param string $key
     *
     * @return bool
     */
    protected function isEmptyString($key)
    {
        $value = $this->input($key);

        return !is_bool($value) && !is_array($value) && trim((string) $value) === '';
    }

    /**
     * Gets a list of languages acceptable by the client browser.
     *
     * @return array Languages ordered in the user browser preferences
     */
    public function getLanguages()
    {
        if (null !== $this->languages) {
            return $this->languages;
        }

        $languages       = AcceptHeader::fromString(current($this->headers->get('Accept-Language')))->all();
        $this->languages = [];
        foreach ($languages as $lang => $acceptHeaderItem) {
            if (false !== strpos($lang, '-')) {
                $codes = explode('-', $lang);
                if ('i' === $codes[0]) {
                    // Language not listed in ISO 639 that are not variants
                    // of any listed language, which can be registered with the
                    // i-prefix, such as i-cherokee
                    if (count($codes) > 1) {
                        $lang = $codes[1];
                    }
                } else {
                    for ($i = 0, $max = count($codes); $i < $max; ++$i) {
                        if ($i === 0) {
                            $lang = strtolower($codes[0]);
                        } else {
                            $lang .= '_' . strtoupper($codes[$i]);
                        }
                    }
                }
            }

            $this->languages[] = $lang;
        }

        return $this->languages;
    }

    /**
     * Returns the preferred language.
     *
     * @param array $locales An array of ordered available locales
     *
     * @return null|string The preferred locale
     */
    public function getPreferredLanguage(array $locales = null)
    {
        $preferredLanguages = $this->getLanguages();

        if (empty($locales)) {
            return isset($preferredLanguages[0]) ? $preferredLanguages[0] : null;
        }

        if (!$preferredLanguages) {
            return $locales[0];
        }

        $extendedPreferredLanguages = [];
        foreach ($preferredLanguages as $language) {
            $extendedPreferredLanguages[] = $language;
            if (false !== $position = strpos($language, '_')) {
                $superLanguage = substr($language, 0, $position);
                if (!in_array($superLanguage, $preferredLanguages)) {
                    $extendedPreferredLanguages[] = $superLanguage;
                }
            }
        }

        $preferredLanguages = array_values(array_intersect($extendedPreferredLanguages, $locales));

        return isset($preferredLanguages[0]) ? $preferredLanguages[0] : $locales[0];
    }
}
