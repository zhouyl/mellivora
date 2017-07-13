<?php

namespace Mellivora\Support;

use ArrayAccess;
use JsonSerializable;
use Mellivora\Support\Arr;
use Mellivora\Support\Contracts\Arrayable;
use Mellivora\Support\Contracts\Jsonable;
use Mellivora\Support\Traits\MagicAccess;

class Fluent implements ArrayAccess, JsonSerializable, Jsonable, Arrayable
{
    use MagicAccess;

    /**
     * All of the attributes set on the container.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Create a new fluent container instance.
     *
     * @param  array|object $attributes
     * @return void
     */
    public function __construct($attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Get an attribute from the container.
     * Set an item on an array or object using dot notation.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->attributes, $key, $default);
    }

    /**
     * Set an attribute from the container.
     *
     * @param  string|array                $key
     * @param  mixed                       $value
     * @return \Mellivora\Support\Fluent
     */
    public function set($key, $value = null)
    {
        $attributes = is_array($key) ? $key : [$key => $value];

        foreach ($attributes as $key => $value) {
            Arr::set($this->attributes, $key, $value);
        }

        return $this;
    }

    /**
     * Determine if the given key exists from the container.
     *
     * @param  string $key
     * @return bool
     */
    public function exists($key)
    {
        return Arr::exists($this->attributes, $key);
    }

    /**
     * Remove an attribute from the container.
     *
     * @param  string                      $key
     * @return \Mellivora\Support\Fluent
     */
    public function remove($key)
    {
        Arr::forget($this->attributes, $key);

        return $this;
    }

    /**
     * Get the attributes from the container.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Convert the Fluent instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the Fluent instance to JSON.
     *
     * @param  int      $options
     * @return string
     */
    public function toJson($options = JSON_ENCODE_OPTION)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
