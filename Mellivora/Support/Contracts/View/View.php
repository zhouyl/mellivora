<?php

namespace Mellivora\Support\Contracts\View;

use Mellivora\Support\Contracts\Renderable;

interface View extends Renderable
{
    /**
     * Get the name of the view.
     *
     * @return string
     */
    public function name();

    /**
     * Add a piece of data to the view.
     *
     * @param  string|array $key
     * @param  mixed        $value
     * @return $this
     */
    public function with($key, $value = null);
}
