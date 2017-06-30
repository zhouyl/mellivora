<?php

namespace Mellivora\Support\Providers;

use Mellivora\Http\Response;
use Mellivora\Support\ServiceProvider;
use Slim\Http\Headers;

class ResponseServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container['response'] = function ($container) {
            $headers  = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
            $response = new Response(200, $headers);

            return $response->withProtocolVersion($container['settings']['httpVersion']);
        };
    }

}
