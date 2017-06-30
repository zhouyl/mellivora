<?php

namespace Mellivora\Database\Console\Seeds;

use Mellivora\Console\GeneratorCommand;
use Mellivora\Filesystem\Filesystem;
use Mellivora\Support\Composer;

class SeederMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:seeder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new seeder class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Seeder';

    /**
     * The Composer instance.
     *
     * @var \Mellivora\Support\Composer
     */
    protected $composer;

    /**
     * Create a new command instance.
     *
     * @param  \Mellivora\Filesystem\Filesystem $files
     * @param  \Mellivora\Support\Composer      $composer
     * @return void
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct($files);

        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        parent::fire();

        $this->composer->dumpAutoloads();
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/seeder.stub';
    }

    /**
     * Get the destination class path.
     *
     * @param  string   $name
     * @return string
     */
    protected function getPath($name)
    {
        return $this->laravel->databasePath() . '/seeds/' . $name . '.php';
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param  string   $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        return $name;
    }
}
