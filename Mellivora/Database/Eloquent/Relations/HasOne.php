<?php

namespace Mellivora\Database\Eloquent\Relations;

use Mellivora\Database\Eloquent\Collection;
use Mellivora\Database\Eloquent\Model;

class HasOne extends HasOneOrMany
{
    /**
     * Indicates if a default model instance should be used.
     *
     * Alternatively, may be a Closure to execute to retrieve default value.
     *
     * @var bool|\Closure
     */
    protected $withDefault;

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->query->first() ?: $this->getDefaultFor($this->parent);
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param array  $models
     * @param string $relation
     *
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->getDefaultFor($model));
        }

        return $models;
    }

    /**
     * Get the default value for this relation.
     *
     * @param \Mellivora\Database\Eloquent\Model $model
     *
     * @return null|\Mellivora\Database\Eloquent\Model
     */
    protected function getDefaultFor(Model $model)
    {
        if (!$this->withDefault) {
            return;
        }

        $instance = $this->related->newInstance()->setAttribute(
            $this->getForeignKeyName(),
            $model->getAttribute($this->localKey)
        );

        if (is_callable($this->withDefault)) {
            return call_user_func($this->withDefault, $instance) ?: $instance;
        }

        if (is_array($this->withDefault)) {
            $instance->forceFill($this->withDefault);
        }

        return $instance;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param array                                   $models
     * @param \Mellivora\Database\Eloquent\Collection $results
     * @param string                                  $relation
     *
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        return $this->matchOne($models, $results, $relation);
    }

    /**
     * Return a new model instance in case the relationship does not exist.
     *
     * @param bool|\Closure $callback
     *
     * @return $this
     */
    public function withDefault($callback = true)
    {
        $this->withDefault = $callback;

        return $this;
    }
}
