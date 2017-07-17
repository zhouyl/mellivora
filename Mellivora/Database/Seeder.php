<?php

namespace Mellivora\Database;

use InvalidArgumentException;
use Mellivora\Application\Container;
use Mellivora\Console\Command;

abstract class Seeder
{
    /**
     * The container instance.
     *
     * @var \Mellivora\Application\Container
     */
    protected $container;

    /**
     * The console command instance.
     *
     * @var \Mellivora\Console\Command
     */
    protected $command;

    /**
     * Constructor
     *
     * @param \Mellivora\Application\Container $container
     * @param \Mellivora\Console\Command       $command
     */
    public function __construct(Container $container, Command $command)
    {
        $this->container = $container;
        $this->command   = $command;
    }

    /**
     * Seed the given connection from the given path.
     *
     * @param  string $class
     * @return void
     */
    public function call($class)
    {
        $this->command->getOutput()->writeln("<info>Seeding:</info> $class");

        $seeder = new $class($this->container, $this->command);

        return $seeder->__invoke();
    }

    /**
     * Run the database seeds.
     *
     * @throws \InvalidArgumentException
     * @return void
     */
    public function __invoke()
    {
        if (!method_exists($this, 'run')) {
            throw new InvalidArgumentException('Method [run] missing from ' . get_class($this));
        }
        $this->command->getOutput()->writeln('<info>Runing:</info> ' . get_class($this));

        return $this->run();
    }
}
