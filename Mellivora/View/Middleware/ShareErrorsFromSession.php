<?php

namespace Mellivora\Support\Middlewares;

use Closure;
use Mellivora\Application\Container;
use Mellivora\Support\Middleware;
use Mellivora\Support\ViewErrorBag;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ShareErrorsFromSession extends Middleware
{

    /**
     * @param Mellivora\Application\Container $container
     */
    protected $container;

    /**
     * @param Mellivora\Application\Container $app
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Invoke middleware
     *
     * @param  Psr\Http\Message\ServerRequestInterface $request
     * @param  Psr\Http\Message\ResponseInterface      $response
     * @param  \Closure                                $next
     * @return Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ResponseInterface $request, ResponseInterface $response, callable $next)
    {
        // If the current session has an "errors" variable bound to it, we will share
        // its value with all view instances so the views can easily access errors
        // without having to bind. An empty bag is set when there aren't errors.
        $this->container['view']->share(
            'errors', $this->container['session']->get('errors') ?: new ViewErrorBag
        );

        // Putting the errors in the view for every view allows the developer to just
        // assume that some errors are always available, which is convenient since
        // they don't have to continually run checks for the presence of errors.

        return $next($request);
    }
}
