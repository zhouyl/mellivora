<?php

namespace Mellivora\Support\Providers;

use Mellivora\Support\Providers\ServiceProvider;
use Slim\Csrf\Guard;

class CsrfServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container['csrf'] = function ($container) {
            $guard = new Guard(
                'csrf',
                $container['session'],
                function ($request, $response, $next) {
                    $request = $request->withAttribute('csrf_status', false);

                    return $next($request, $response);
                }
            );

            return $guard;
        };

        // add the middleware
        $this->app->add($this->container['csrf']);
    }

}
