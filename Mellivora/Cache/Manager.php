<?php

namespace Mellivora\Cache;

use UnexpectedValueException;

class Manager
{

    protected $default = 'null';

    protected $drivers    = [];
    protected $connectors = [];

    public function __construct(array $drivers = [])
    {
        $this->drivers = array_change_key_case($drivers, CASE_LOWER) + ['null' => []];
    }

    public function setDefault($name)
    {
        $name = strtolower($name);

        if (!isset($this->drivers[$name])) {
            throw new UnexpectedValueException(
                "Unregistered cache driver name '$name'");
        }

        $this->default = $name;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function setDrivers(array $drivers)
    {
        foreach ($drivers as $driver => $config) {
            $this->setDriver($driver, $config);
        }

        return $this;
    }

    public function setDriver($driver, array $config)
    {
        if (!isset($config['connector'])) {
            $config['connector'] = NullConnector::class;
        }

        $this->drivers[$driver] = $config;

        return $this;
    }

    protected function getConnector($name)
    {
        if (!$config = $this->drivers[$name] ?? false) {
            throw new UnexpectedValueException(
                "Unregistered cache driver name '$name'");
        }

        if (!isset($this->connectors[$name])) {
            if (!is_subclass_of($config['connector'], ConnectorInterface::class)) {
                throw new UnexpectedValueException(
                    $config['connector'] . ' must return instance of ' . ConnectorInterface::class);
            }

            $connector = new $config['connector']($config);

            $this->connectors[$name] = $connector;
        }

        return $this->connectors[$name];
    }

    public function getCache($name)
    {
        return $this->getConnector($name)->getCacheAdapter();
    }

    public function getSimpleCache($name)
    {
        return $this->getConnector($name)->getSimpleCacheAdapter();
    }

    public function getDefaultCache()
    {
        return $this->getCache($this->default);
    }

    public function getDefaultSimpleCache()
    {
        return $this->getSimpleCache($this->default);
    }
}
