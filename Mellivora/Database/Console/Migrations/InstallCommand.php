<?php

namespace Mellivora\Database\Console\Migrations;

use Mellivora\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class InstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'migrate:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the migration repository';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->container['migration.repository']->setSource($this->input->getOption('database'));

        $this->container['migration.repository']->createRepository();

        $this->info('Migration table created successfully.');
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
        ];
    }
}
