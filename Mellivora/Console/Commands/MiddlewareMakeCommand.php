<?php

namespace Mellivora\Console\Commands;

use Mellivora\Console\GeneratorCommand;

class MiddlewareMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:middleware';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new middleware class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Middleware';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/middleware.stub';
    }

    /**
     * Get the name suffix
     *
     * @return false|string
     */
    protected function getNameSuffix()
    {
        return 'Middleware';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Middlewares';
    }
}
