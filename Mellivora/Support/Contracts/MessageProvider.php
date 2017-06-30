<?php

namespace Mellivora\Support\Contracts;

interface MessageProvider
{
    /**
     * Get the messages for the instance.
     *
     * @return \Mellivora\Support\Contracts\MessageBag
     */
    public function getMessageBag();
}
