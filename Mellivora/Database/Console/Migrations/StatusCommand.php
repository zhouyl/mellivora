<?php

namespace Mellivora\Database\Console\Migrations;

use Mellivora\Support\Collection;
use Symfony\Component\Console\Input\InputOption;

class StatusCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'migrate:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the status of each migration';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $migrator = $this->container['migrator'];

        $migrator->setConnection($this->option('database'));

        if (!$migrator->repositoryExists()) {
            return $this->error('No migrations found.');
        }

        $ran = $migrator->getRepository()->getRan();

        if (count($migrations = $this->getStatusFor($ran)) > 0) {
            $this->table(['Ran?', 'Migration'], $migrations);
        } else {
            $this->error('No migrations found');
        }
    }

    /**
     * Get the status for the given ran migrations.
     *
     * @param  array                           $ran
     * @return \Mellivora\Support\Collection
     */
    protected function getStatusFor(array $ran)
    {
        return Collection::make($this->getAllMigrationFiles())
            ->map(function ($migration) use ($ran) {
                $migrationName = $this->container['migrator']->getMigrationName($migration);

                return in_array($migrationName, $ran)
                    ? ['<info>Y</info>', $migrationName]
                    : ['<fg=red>N</fg=red>', $migrationName];
            });
    }

    /**
     * Get an array of all of the migration files.
     *
     * @return array
     */
    protected function getAllMigrationFiles()
    {
        return $this->container['migrator']
            ->getMigrationFiles($this->getMigrationPaths());
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'],

            ['path', null, InputOption::VALUE_OPTIONAL, 'The path of migrations files to use.'],
        ];
    }
}
