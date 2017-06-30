<?php

namespace Mellivora\Database;

use InvalidArgumentException;
use Mellivora\Console\Command;
use Mellivora\Container\Container;

abstract class Seeder
{
    /**
     * The container instance.
     *
     * @var \Mellivora\Container\Container
     */
    protected $container;

    /**
     * The console command instance.
     *
     * @var \Mellivora\Console\Command
     */
    protected $command;

    /**
     * Seed the given connection from the given path.
     *
     * @param  string $class
     * @return void
     */
    public function call($class)
    {
        if (isset($this->command)) {
            $this->command->getOutput()->writeln("<info>Seeding:</info> $class");
        }

        $this->resolve($class)->__invoke();
    }

    /**
     * Resolve an instance of the given seeder class.
     *
     * @param  string                       $class
     * @return \Mellivora\Database\Seeder
     */
    protected function resolve($class)
    {
        if (isset($this->container)) {
            $instance = $this->container->make($class);

            $instance->setContainer($this->container);
        } else {
            $instance = new $class;
        }

        if (isset($this->command)) {
            $instance->setCommand($this->command);
        }

        return $instance;
    }

    /**
     * Set the IoC container instance.
     *
     * @param  \Mellivora\Container\Container $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Set the console command instance.
     *
     * @param  \Mellivora\Console\Command $command
     * @return $this
     */
    public function setCommand(Command $command)
    {
        $this->command = $command;

        return $this;
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

        return isset($this->container)
            ? $this->container->call([$this, 'run'])
            : $this->run();
    }
}
