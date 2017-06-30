<?php

namespace Mellivora\View\Middleware;

use Closure;
use Mellivora\Application\Container;
use Mellivora\Support\ViewErrorBag;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ShareErrorsFromSession
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
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $this->container['view']->share(
            'errors', $this->container['session']->get('errors') ?: new ViewErrorBag
        );

        return $next($request, $response);
    }
}
