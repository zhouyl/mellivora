<?php

namespace Mellivora\Database\Console\Seeds;

use Mellivora\Application\Container;
use Mellivora\Console\Command;
use Mellivora\Console\ConfirmableTrait;
use Mellivora\Database\Eloquent\Model;
use Symfony\Component\Console\Input\InputArgument;
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
        $class = $this->getClassName();

        if (!class_exists($class)) {
            $file = database_path('/seeds/' . $class . '.php');

            if (is_file($file)) {
                require_once $file;
            }
        }

        return new $class($this->container, $this);
    }

    /**
     * Get class name of the seeder
     *
     * @return string
     */
    protected function getClassName()
    {
        $class = studly_case($this->input->getArgument('class'));

        if (strpos($class, 'Seeder') == false) {
            $class .= 'Seeder';
        }

        return $class;
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
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['class', InputArgument::REQUIRED, 'The class name of the root seeder'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to seed'],

            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
        ];
    }
}
