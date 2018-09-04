<?php

namespace Mellivora\Database\Console\Migrations;

class MigrateMakeCommand extends BaseCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'make:migration {name : The name of the migration.}
        {--create= : The table to be created.}
        {--table= : The table to migrate.}
        {--path= : The location where the migration file should be created.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration file';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        // It's possible for the developer to specify the tables to modify in this
        // schema operation. The developer may also specify if this table needs
        // to be freshly created so we can create the appropriate migrations.
        $name = trim($this->input->getArgument('name'));

        $table = $this->input->getOption('table');

        $create = $this->input->getOption('create') ?: false;

        // If no table was given as an option but a create option is given then we
        // will use the "create" option as the table name. This allows the devs
        // to pass a table name into this option as a short-cut for creating.
        if (!$table && is_string($create)) {
            $table = $create;

            $create = true;
        }

        // Now we are ready to write the migration out to disk. Once we've written
        // the migration out, we will dump-autoload for the entire framework to
        // make sure that the migrations are registered by the class loaders.
        $this->writeMigration($name, $table, $create);
    }

    /**
     * Write the migration file to disk.
     *
     * @param string $name
     * @param string $table
     * @param bool   $create
     *
     * @return string
     */
    protected function writeMigration($name, $table, $create)
    {
        $file = pathinfo($this->container['migration.creator']->create(
            $name,
            $this->getMigrationPath(),
            $table,
            $create
        ), PATHINFO_FILENAME);

        $this->line("<info>Created Migration:</info> {$file}");
    }

    /**
     * Get migration path (either specified by '--path' option or default location).
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        if (!is_null($targetPath = $this->input->getOption('path'))) {
            return root_path($targetPath);
        }

        return parent::getMigrationPath();
    }
}
