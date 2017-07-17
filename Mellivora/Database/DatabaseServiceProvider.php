<?php

namespace Mellivora\Database;

use Faker\Factory as FakerFactory;
use Mellivora\Database\Connectors\ConnectionFactory;
use Mellivora\Database\Eloquent\Factory as EloquentFactory;
use Mellivora\Database\Eloquent\Model;
use Mellivora\Database\Eloquent\QueueEntityResolver;
use Mellivora\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConnectionServices();
        $this->registerEloquentFactory();
        $this->registerQueueableEntityResolver();

        Model::setConnectionResolver($this->container['db']);
        Model::setEventDispatcher($this->container['events']);
        Model::clearBootedModels();
    }

    /**
     * Register the primary database bindings.
     *
     * @return void
     */
    protected function registerConnectionServices()
    {
        // The connection factory is used to create the actual connection instances on
        // the database. We will inject the factory into the manager so that it may
        // make the connections while they are actually needed and not of before.
        $this->container['db.factory'] = function ($container) {
            return new ConnectionFactory($container);
        };

        // The database manager is used to resolve various connections, since multiple
        // connections might be managed. It also implements the connection resolver
        // interface which may be used by other components requiring connections.
        $this->container['db'] = function ($container) {
            return new DatabaseManager($container, $container['db.factory']);
        };

        $this->container['db.connection'] = function ($container) {
            return $container['db']->connection();
        };
    }

    /**
     * Register the Eloquent factory instance in the container.
     *
     * @return void
     */
    protected function registerEloquentFactory()
    {
        $this->container['db.faker'] = function () {
            return FakerFactory::create();
        };

        $this->container['db.eloquent'] = function ($container) {
            return EloquentFactory::construct(
                $container['db.faker'], database_path('factories')
            );
        };
    }

    /**
     * Register the queueable entity resolver implementation.
     *
     * @return void
     */
    protected function registerQueueableEntityResolver()
    {
        $this->container['db.entity'] = function () {
            return new QueueEntityResolver;
        };
    }
}
