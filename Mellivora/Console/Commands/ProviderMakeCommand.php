<?php

namespace Mellivora\Console\Commands;

use Mellivora\Console\GeneratorCommand;
use Mellivora\Support\Str;

class ProviderMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:provider';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new provider class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Provider';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/provider.stub';
    }

    /**
     * Get the name suffix
     *
     * @return string|false
     */
    protected function getNameSuffix()
    {
        return 'ServiceProvider';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string   $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Providers';
    }
}
