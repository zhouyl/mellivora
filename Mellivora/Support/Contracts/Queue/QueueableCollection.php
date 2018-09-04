<?php

namespace Mellivora\Support\Contracts\Queue;

interface QueueableCollection
{
    /**
     * Get the type of the entities being queued.
     *
     * @return null|string
     */
    public function getQueueableClass();

    /**
     * Get the identifiers for all of the entities.
     *
     * @return array
     */
    public function getQueueableIds();
}
