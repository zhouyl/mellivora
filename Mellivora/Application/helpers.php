<?php

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param  string  $id
     * @return mixed
     */
    function app($id = null)
    {
        $container = Mellivora\Application\App::instance()->getContainer();

        return $id ? $container->get($id) : $container;
    }
}

if (!function_exists('env')) {
    /**
     * 判断当前项目环境，或者获取当前项目环境
     *
     * @param  string           $asset
     * @return boolean|string
     */
    function env($environment = null)
    {
        if ($environment === null) {
            return app('settings')['environment'];
        }

        return strtolower($environment) === app('settings')['environment'];
    }
}

if (!function_exists('cache')) {
    /**
     * Get / set the specified cache value.
     *
     * If an array is passed, we'll assume you want to put to the cache.
     *
     * @param  dynamic      key|key,default|data,expiration|null
     * @throws \Exception
     * @return mixed
     */
    function cache() {}
}

if (!function_exists('config')) {
    /**
     * Get the specified configuration value.
     *
     * @param  array   $key
     * @param  mixed   $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        if ($key === null) {
            return app('config');
        }

        return app('config')->get($key, $default);
    }
}

if (!function_exists('cookie')) {
    /**
     * Create a new cookie instance.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  int     $minutes
     * @return mixed
     */
    function cookie($name = null, $value = null, $minutes = 0)
    {
        if (func_num_args() === 0) {
            return app('cookies');
        }

        if (func_num_args() === 1) {
            return app('cookies')->get($name);
        }

        return app('cookies')->set($name, $value, $minutes);
    }
}

if (!function_exists('session')) {
    /**
     * Get / set the specified session value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string $key
     * @param  mixed        $default
     * @return mixed
     */
    function session($key = null, $default = null) {}
}

if (!function_exists('trans')) {
    /**
     * Translate the given message.
     *
     * @param  string                                                $id
     * @param  array                                                 $replace
     * @param  string                                                $locale
     * @return \Illuminate\Contracts\Translation\Translator|string
     */
    function trans($id = null, $replace = [], $locale = null) {}
}

if (!function_exists('trans_choice')) {
    /**
     * Translates the given message based on a count.
     *
     * @param  string               $id
     * @param  int|array|\Countable $number
     * @param  array                $replace
     * @param  string               $locale
     * @return string
     */
    function trans_choice($id, $number, array $replace = [], $locale = null)
    {
        return app('translator')->transChoice($id, $number, $replace, $locale);
    }
}

if (!function_exists('__')) {
    /**
     * Translate the given message.
     *
     * @param  string                                                $key
     * @param  array                                                 $replace
     * @param  string                                                $locale
     * @return \Illuminate\Contracts\Translation\Translator|string
     */
    function __($key = null, $replace = [], $locale = null) {}
}

if (!function_exists('view')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string                 $view
     * @param  array                  $data
     * @param  callable|null          $callback
     * @return \Mellivora\View\View
     */
    function view($view = null, $data = [], callable $callback = null)
    {
        if (func_num_args() === 0) {
            return app('view');
        }

        return app('view')->make($view, $data)->render($callback);
    }
}

if (!function_exists('redirect')) {
    /**
     * Get an instance of the redirector.
     *
     * @param  string                    $to
     * @param  int                       $status
     * @return Mellivora\Http\Response
     */
    function redirect($to, $status = null)
    {
        return app('response')->redirect($to, $status);
    }
}

if (!function_exists('request')) {
    /**
     * Get an instance of the current request or an input item from the request.
     *
     * @param  array|string                          $key
     * @param  mixed                                 $default
     * @return Mellivora\Http\Request|string|array
     */
    function request($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('request');
        }

        if (is_array($key)) {
            return app('request')->only($key);
        }

        return data_get(app('request')->all(), $key, $default);
    }
}

if (!function_exists('response')) {
    /**
     * Return a new response from the application.
     *
     * @param  string                    $content
     * @param  int                       $status
     * @param  array                     $headers
     * @return Mellivora\Http\Response
     */
    function response($content = '', $status = 200, array $headers = [])
    {
        if (func_num_args() === 0) {
            return app('response');
        }

        return app('response')->withStatus($status)
            ->withHeaders($headers)
            ->write($content);
    }
}

if (!function_exists('route')) {
    /**
     * Build the path for a named route including the base path
     *
     * @param  string   $name        Route name
     * @param  array    $data        Named argument replacement data
     * @param  array    $queryParams Optional query string parameters
     * @return string
     */
    function route($name, array $data = [], array $queryParams = [])
    {
        return app('router')->pathFor($name, $data, $queryParams);
    }
}

if (!function_exists('url')) {
    /**
     * Generate a url for the application.
     *
     * @param  string   $path
     * @param  array    $queryParams
     * @return string
     */
    function url($path = null, array $queryParams = [])
    {
        if ($path === null) {
            return app('request')->fullUrlWithQuery($queryParams);
        }

        if (preg_match('~^https?://~', $path)) {
            $uri = Slim\Http\Uri::createFromString($path);
        } else {
            $parts = parse_url($path) + ['path' => '', 'query' => '', 'fragment' => ''];

            $uri = app('request')->getUri()
                ->withPath($parts['path'])
                ->withQuery($parts['query'])
                ->withFragment($parts['fragment']);
        }

        parse_str($uri->getQuery(), $queryParts);

        return (string) $uri->withQuery(http_build_query($queryParams + $queryParts));
    }
}

if (!function_exists('url_spintf')) {
    /**
     * 使用 sprintf 格式化生成 url
     *
     * @param  string   $format
     * @param  string   ...$args
     * @return string
     */
    function url_spintf($format, ...$args)
    {
        return url(vsprintf($format, $args));
    }
}

if (!function_exists('root_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string   $path
     * @return string
     */
    function root_path($path = '')
    {
        return normalize_path(__ROOT__ . DIRECTORY_SEPARATOR . $path);
    }
}

if (!function_exists('app_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string   $path
     * @return string
     */
    function app_path($path = '')
    {
        return root_path('app/' . $path);
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the data path.
     *
     * @param  string   $path
     * @return string
     */
    function storage_path($path = '')
    {
        return root_path('storage/' . $path);
    }
}

if (!function_exists('resource_path')) {
    /**
     * Get the path to the resource folder.
     *
     * @param  string   $path
     * @return string
     */
    function resource_path($path = '')
    {
        return root_path('resources/' . $path);
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param  string   $path
     * @return string
     */
    function public_path($path = '')
    {
        return root_path('public/' . $path);
    }
}
