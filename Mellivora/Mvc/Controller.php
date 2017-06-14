<?php

namespace Mellivora\MVC;

use Mellivora\Application\Container;

class Controller
{

    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __get($id)
    {
        return $this->container->get($id);
    }
}
