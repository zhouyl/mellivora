<?php

namespace Mellivora\Support\Contracts\Queue;

interface Factory
{
    /**
     * Resolve a queue connection instance.
     *
     * @param  string                                     $name
     * @return \Mellivora\Support\Contracts\Queue\Queue
     */
    public function connection($name = null);
}
