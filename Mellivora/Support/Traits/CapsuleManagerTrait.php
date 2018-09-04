<?php

namespace Mellivora\Support\Traits;

use Mellivora\Application\Container;
use Mellivora\Support\Fluent;

trait CapsuleManagerTrait
{
    /**
     * The container instance.
     *
     * @var \Mellivora\Application\Container
     */
    protected $container;

    /**
     * Setup the IoC container instance.
     *
     * @param \Mellivora\Application\Container $container
     *
     * @return void
     */
    protected function setupContainer(Container $container)
    {
        $this->container = $container;

        if (!$this->container->has('config')) {
            $this->container['config'] = new Fluent;
        }
    }

    /**
     * Get the IoC container instance.
     *
     * @return \Mellivora\Application\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the IoC container instance.
     *
     * @param \Mellivora\Application\Container $container
     *
     * @return void
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }
}
