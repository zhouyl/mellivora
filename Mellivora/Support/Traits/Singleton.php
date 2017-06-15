<?php

namespace Mellivora\Support\Traits;

trait Singleton
{

    protected static $instance;

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function registerSingleton()
    {
        self::$instance = $this;

        return $this;
    }
}
