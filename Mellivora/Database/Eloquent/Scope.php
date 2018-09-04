<?php

namespace Mellivora\Database\Eloquent;

interface Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Mellivora\Database\Eloquent\Builder $builder
     * @param \Mellivora\Database\Eloquent\Model   $model
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model);
}
