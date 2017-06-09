<?php

namespace Mellivora;

use Slim\Container as SlimContainer;

class Container extends SlimContainer
{
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this->registerAliases();
        $this->registerProviders();
    }

    protected function registerAliases()
    {
        if ($this->has('aliases')) {
            foreach ($this->get('aliases') as $alias => $abstract) {
                $this->alias($abstract, $alias);
            }
        }
    }

    protected function registerProviders()
    {
        if ($this->has('providers')) {
            foreach ($this->get('providers') as $class) {
                (new $class())->register($this);
            }
        }
    }

    public function set($id, $value)
    {
        return $this->offsetSet($id, $value);
    }

    public function __set($id, $value)
    {
        return $this->offsetSet($id, $value);
    }

    public function alias($abstract, $alias)
    {
        class_alias($abstract, $alias);

        $aliases = $this->has('aliases') ? $this->get('aliases') : [];

        $aliases[$alias] = $abstract;

        $this->offsetSet('aliases', $aliases);
    }
}
