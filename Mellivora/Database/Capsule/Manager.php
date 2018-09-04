<?php

namespace Mellivora\Database\Capsule;

use Mellivora\Application\Container;
use Mellivora\Database\Connectors\ConnectionFactory;
use Mellivora\Database\DatabaseManager;
use Mellivora\Database\Eloquent\Model as Eloquent;
use Mellivora\Support\Contracts\Events\Dispatcher;
use Mellivora\Support\Traits\CapsuleManagerTrait;
use Mellivora\Support\Traits\Singleton;
use PDO;

class Manager
{
    use CapsuleManagerTrait;
    use Singleton;

    /**
     * The database manager instance.
     *
     * @var \Mellivora\Database\DatabaseManager
     */
    protected $manager;

    /**
     * Create a new database capsule manager.
     *
     * @param null|\Mellivora\Application\Container $container
     *
     * @return void
     */
    public function __construct(Container $container = null)
    {
        $this->setupContainer($container ?: new Container);

        // Once we have the container setup, we will setup the default configuration
        // options in the container "config" binding. This will make the database
        // manager work correctly out of the box without extreme configuration.
        $this->setupDefaultConfiguration();

        $this->setupManager();
    }

    /**
     * Setup the default database configuration options.
     *
     * @return void
     */
    protected function setupDefaultConfiguration()
    {
        $this->container['config']['database.fetch'] = PDO::FETCH_OBJ;

        $this->container['config']['database.default'] = 'default';
    }

    /**
     * Build the database manager instance.
     *
     * @return void
     */
    protected function setupManager()
    {
        $factory = new ConnectionFactory($this->container);

        $this->manager = new DatabaseManager($this->container, $factory);
    }

    /**
     * Get a connection instance from the global manager.
     *
     * @param string $connection
     *
     * @return \Mellivora\Database\Connection
     */
    public static function connection($connection = null)
    {
        return static::$instance->getConnection($connection);
    }

    /**
     * Get a fluent query builder instance.
     *
     * @param string $table
     * @param string $connection
     *
     * @return \Mellivora\Database\Query\Builder
     */
    public static function table($table, $connection = null)
    {
        return static::$instance->connection($connection)->table($table);
    }

    /**
     * Get a schema builder instance.
     *
     * @param string $connection
     *
     * @return \Mellivora\Database\Schema\Builder
     */
    public static function schema($connection = null)
    {
        return static::$instance->connection($connection)->getSchemaBuilder();
    }

    /**
     * Get a registered connection instance.
     *
     * @param string $name
     *
     * @return \Mellivora\Database\Connection
     */
    public function getConnection($name = null)
    {
        return $this->manager->connection($name);
    }

    /**
     * Register a connection with the manager.
     *
     * @param array  $config
     * @param string $name
     *
     * @return void
     */
    public function addConnection(array $config, $name = 'default')
    {
        $connections = $this->container['config']['database.connections'];

        $connections[$name] = $config;

        $this->container['config']['database.connections'] = $connections;
    }

    /**
     * Bootstrap Eloquent so it is ready for usage.
     *
     * @return void
     */
    public function bootEloquent()
    {
        Eloquent::setConnectionResolver($this->manager);

        // If we have an event dispatcher instance, we will go ahead and register it
        // with the Eloquent ORM, allowing for model callbacks while creating and
        // updating "model" instances; however, it is not necessary to operate.
        if ($dispatcher = $this->getEventDispatcher()) {
            Eloquent::setEventDispatcher($dispatcher);
        }
    }

    /**
     * Set the fetch mode for the database connections.
     *
     * @param int $fetchMode
     *
     * @return $this
     */
    public function setFetchMode($fetchMode)
    {
        $this->container['config']['database.fetch'] = $fetchMode;

        return $this;
    }

    /**
     * Get the database manager instance.
     *
     * @return \Mellivora\Database\DatabaseManager
     */
    public function getDatabaseManager()
    {
        return $this->manager;
    }

    /**
     * Get the current event dispatcher instance.
     *
     * @return null|\Mellivora\Support\Contracts\Events\Dispatcher
     */
    public function getEventDispatcher()
    {
        if ($this->container->has('events')) {
            return $this->container['events'];
        }
    }

    /**
     * Set the event dispatcher instance to be used by connections.
     *
     * @param \Mellivora\Support\Contracts\Events\Dispatcher $dispatcher
     *
     * @return void
     */
    public function setEventDispatcher(Dispatcher $dispatcher)
    {
        $this->container['events'] = $dispatcher;
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return static::connection()->{$method}(...$parameters);
    }
}
