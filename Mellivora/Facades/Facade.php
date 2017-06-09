<?php

namespace Mellivora\Facades;

class Facade
{
    protected static $container;

    protected static $resolvedInstance;

    protected static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = static::getFacadeInstance();
        }

        return static::$instance;
    }

    public static function getContainer()
    {
        return static::$container;
    }

    public static function setContainer($container)
    {
        static::$container = $container;
    }

    public static function getFacadeRoot()
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }

    protected static function getFacadeAccessor()
    {
        throw new Exception('Facade does not implement getFacadeAccessor method.');
    }

    protected static function resolveFacadeInstance($name)
    {
        if (is_object($name)) {
            return $name;
        }

        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }

        return static::$resolvedInstance[$name] = static::$container[$name];
    }

    public static function clearResolvedInstances()
    {
        static::$resolvedInstance = [];
    }

    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeRoot();

        if (!$instance) {
            throw new Exception('A facade root has not been set.');
        }

        return $instance->$method(...$args);
    }
}
