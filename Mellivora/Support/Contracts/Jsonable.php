<?php

namespace Mellivora\Support\Contracts;

interface Jsonable
{
    /**
     * Convert the object to its JSON representation.
     *
     * @param  int      $options
     * @return string
     */
    public function toJson($options = JSON_ENCODE_OPTION);
}
