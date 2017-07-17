<?php

namespace Mellivora\Database\Console\Seeds;

use Mellivora\Application\Container;
use Mellivora\Console\Command;
use Mellivora\Console\ConfirmableTrait;
use Mellivora\Database\Eloquent\Model;
use Mellivora\Database\Seeder\NullSeeder;
use Symfony\Component\Console\Input\InputOption;

class SeedCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with records';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $this->container['db']->setDefaultConnection($this->getDatabase());

        Model::unguarded(function () {
            $this->getSeeder()->__invoke();
        });
    }

    /**
     * Get a seeder instance from the container.
     *
     * @return \Mellivora\Database\Seeder
     */
    protected function getSeeder()
    {
        $class = $this->input->getOption('class');

        return new $class($this->container, $this);
    }

    /**
     * Get the name of the database connection to use.
     *
     * @return string
     */
    protected function getDatabase()
    {
        $database = $this->input->getOption('database');

        return $database ?: $this->container['config']['database.default'];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['class', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder', NullSeeder::class],

            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to seed'],

            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
        ];
    }
}
