<?php

namespace Mellivora\Database\Eloquent\Concerns;

use Mellivora\Support\Contracts\Events\Dispatcher;

trait HasEvents
{
    /**
     * User exposed observable events.
     *
     * These are extra user-defined events observers may subscribe to.
     *
     * @var array
     */
    protected static $observables = [
        'booting', 'booted', 'creating', 'created', 'updating', 'updated',
        'deleting', 'deleted', 'saving', 'saved', 'restoring', 'restored',
    ];

    /**
     * The event map for the model.
     *
     * Allows for object-based events for native Eloquent events.
     *
     * @var array
     */
    protected $events = [];

    /**
     * Register an observer with the Model.
     *
     * @param object|string $class
     *
     * @return void
     */
    public static function observe($class)
    {
        $className = is_string($class) ? $class : get_class($class);

        // When registering a model observer, we will spin through the possible events
        // and determine if this observer has that method. If it does, we will hook
        // it into the model's event system, making it convenient to watch these.
        foreach (static::$observables as $event) {
            if (method_exists($class, $event)) {
                static::registerModelEvent($event, $className . '@' . $event);
            }
        }
    }

    /**
     * Register a model event with the dispatcher.
     *
     * @param string          $event
     * @param \Closure|string $callback
     *
     * @return void
     */
    protected static function registerModelEvent($event, $callback)
    {
        if (isset(static::$dispatcher)) {
            static::$dispatcher->listen("eloquent.{$event}", $callback);
        }
    }

    /**
     * Fire the given event for the model.
     *
     * @param string $event
     * @param bool   $halt
     *
     * @return mixed
     */
    protected function fireModelEvent($event, $halt = true)
    {
        if (!isset(static::$dispatcher)) {
            return true;
        }

        // First, we will get the proper method to call on the event dispatcher, and then we
        // will attempt to fire a custom, object based event for the given event. If that
        // returns a result we can return that result, or we'll call the string events.
        $method = $halt ? 'until' : 'fire';

        $result = $this->fireCustomModelEvent($event, $method);

        return !is_null($result) ? $result : static::$dispatcher->{$method}(
            "eloquent.{$event}",
            $this
        );
    }

    /**
     * Fire a custom model event for the given event.
     *
     * @param string $event
     * @param string $method
     *
     * @return null|mixed
     */
    protected function fireCustomModelEvent($event, $method)
    {
        if (!isset($this->events[$event])) {
            return;
        }

        $result = static::$dispatcher->{$method}(new $this->events[$event]($this));

        if (!is_null($result)) {
            return $result;
        }
    }

    /**
     * Register a saving model event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    public static function saving($callback)
    {
        static::registerModelEvent('saving', $callback);
    }

    /**
     * Register a saved model event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    public static function saved($callback)
    {
        static::registerModelEvent('saved', $callback);
    }

    /**
     * Register an updating model event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    public static function updating($callback)
    {
        static::registerModelEvent('updating', $callback);
    }

    /**
     * Register an updated model event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    public static function updated($callback)
    {
        static::registerModelEvent('updated', $callback);
    }

    /**
     * Register a creating model event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    public static function creating($callback)
    {
        static::registerModelEvent('creating', $callback);
    }

    /**
     * Register a created model event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    public static function created($callback)
    {
        static::registerModelEvent('created', $callback);
    }

    /**
     * Register a deleting model event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    public static function deleting($callback)
    {
        static::registerModelEvent('deleting', $callback);
    }

    /**
     * Register a deleted model event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    public static function deleted($callback)
    {
        static::registerModelEvent('deleted', $callback);
    }

    /**
     * Remove all of the event listeners for the model.
     *
     * @return void
     */
    public static function flushEventListeners()
    {
        if (!isset(static::$dispatcher)) {
            return;
        }

        $instance = new static;

        foreach ($instance->getObservableEvents() as $event) {
            static::$dispatcher->forget("eloquent.{$event}: " . static::class);
        }
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return \Mellivora\Support\Contracts\Events\Dispatcher
     */
    public static function getEventDispatcher()
    {
        return static::$dispatcher;
    }

    /**
     * Set the event dispatcher instance.
     *
     * @param \Mellivora\Support\Contracts\Events\Dispatcher $dispatcher
     *
     * @return void
     */
    public static function setEventDispatcher(Dispatcher $dispatcher)
    {
        static::$dispatcher = $dispatcher;
    }

    /**
     * Unset the event dispatcher for models.
     *
     * @return void
     */
    public static function unsetEventDispatcher()
    {
        static::$dispatcher = null;
    }
}
