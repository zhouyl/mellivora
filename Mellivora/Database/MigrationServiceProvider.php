<?php

namespace Mellivora\Database;

use Mellivora\Database\Migrations\DatabaseMigrationRepository;
use Mellivora\Database\Migrations\MigrationCreator;
use Mellivora\Database\Migrations\Migrator;
use Mellivora\Support\ServiceProvider;

class MigrationServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRepository();
        $this->registerMigrator();
        $this->registerCreator();
    }

    /**
     * Register the migration repository service.
     *
     * @return void
     */
    protected function registerRepository()
    {
        $this->container['migration.repository'] = function ($container) {
            $table = $container['config']['database.migrations'];

            return new DatabaseMigrationRepository($container['db'], $table);
        };
    }

    /**
     * Register the migrator service.
     *
     * @return void
     */
    protected function registerMigrator()
    {
        // The migrator is responsible for actually running and rollback the migration
        // files in the application. We'll pass in our database connection resolver
        // so the migrator can resolve any of these connections when it needs to.
        $this->container['migrator'] = function ($container) {
            $repository = $container['migration.repository'];

            return new Migrator($repository, $container['db']);
        };
    }

    /**
     * Register the migration creator.
     *
     * @return void
     */
    protected function registerCreator()
    {
        $this->container['migration.creator'] = function ($container) {
            return new MigrationCreator;
        };
    }
}
