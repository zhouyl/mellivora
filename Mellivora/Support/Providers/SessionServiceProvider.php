<?php

namespace Mellivora\Support\Providers;

use InvalidArgumentException;
use Mellivora\Session\Session;
use Mellivora\Support\ServiceProvider;
use SessionHandlerInterface;

class SessionServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container['session'] = function ($container) {
            $handler = null;
            if ($config = $container['config']->get('session.saveHandler')) {
                if (!$class = $config->handler) {
                    throw new InvalidArgumentException(
                        'Invalid "handler" parameter in the session save handler');
                }

                if (!is_subclass_of($class, SessionHandlerInterface::class)) {
                    throw new InvalidArgumentException(
                        $class . ' must implement of ' . SessionHandlerInterface::class);
                }

                $handler = new $class($config->options ? $config->options->toArray() : null);
            }

            $session = new Session($handler);
            $session->start();

            return $session;
        };
    }
}
