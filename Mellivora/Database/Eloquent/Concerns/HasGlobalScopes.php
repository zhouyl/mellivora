<?php

namespace Mellivora\Database\Eloquent\Concerns;

use Closure;
use InvalidArgumentException;
use Mellivora\Database\Eloquent\Scope;
use Mellivora\Support\Arr;

trait HasGlobalScopes
{
    /**
     * Register a new global scope on the model.
     *
     * @param \Closure|\Mellivora\Database\Eloquent\Scope|string $scope
     * @param null|\Closure                                      $implementation
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public static function addGlobalScope($scope, Closure $implementation = null)
    {
        if (is_string($scope) && !is_null($implementation)) {
            return static::$globalScopes[static::class][$scope] = $implementation;
        }
        if ($scope instanceof Closure) {
            return static::$globalScopes[static::class][spl_object_hash($scope)] = $scope;
        }
        if ($scope instanceof Scope) {
            return static::$globalScopes[static::class][get_class($scope)] = $scope;
        }

        throw new InvalidArgumentException('Global scope must be an instance of Closure or Scope.');
    }

    /**
     * Determine if a model has a global scope.
     *
     * @param \Mellivora\Database\Eloquent\Scope|string $scope
     *
     * @return bool
     */
    public static function hasGlobalScope($scope)
    {
        return !is_null(static::getGlobalScope($scope));
    }

    /**
     * Get a global scope registered with the model.
     *
     * @param \Mellivora\Database\Eloquent\Scope|string $scope
     *
     * @return null|\Closure|\Mellivora\Database\Eloquent\Scope
     */
    public static function getGlobalScope($scope)
    {
        if (is_string($scope)) {
            return Arr::get(static::$globalScopes, static::class . '.' . $scope);
        }

        return Arr::get(
            static::$globalScopes,
            static::class . '.' . get_class($scope)
        );
    }

    /**
     * Get the global scopes for this class instance.
     *
     * @return array
     */
    public function getGlobalScopes()
    {
        return Arr::get(static::$globalScopes, static::class, []);
    }
}
