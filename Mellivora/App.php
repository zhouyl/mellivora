<?php

namespace Mellivora;

use Mellivora\Facades\Facade;
use Slim\App as SlimApp;

class App extends SlimApp
{

    /**
     * {@inheritdoc}
     */
    public function __construct($container = [])
    {
        parent::__construct($container);

        Facade::setContainer($this->getContainer());
    }

    /**
     * {@inheritdoc}
     */
    public function run($silent = false)
    {
        return parent::run($silent);
    }
}
