<?php

namespace Mellivora\Database\Eloquent;

use Mellivora\Support\Contracts\Queue\EntityNotFoundException;
use Mellivora\Support\Contracts\Queue\EntityResolver as EntityResolverContract;

class QueueEntityResolver implements EntityResolverContract
{
    /**
     * Resolve the entity for the given ID.
     *
     * @param  string                                                       $type
     * @param  mixed                                                        $id
     * @throws \Mellivora\Support\Contracts\Queue\EntityNotFoundException
     * @return mixed
     */
    public function resolve($type, $id)
    {
        $instance = (new $type)->find($id);

        if ($instance) {
            return $instance;
        }

        throw new EntityNotFoundException($type, $id);
    }
}
