<?php

namespace Mellivora\View\Compilers;

use InvalidArgumentException;

abstract class Compiler
{
    /**
     * Get the cache path for the compiled views.
     *
     * @var string
     */
    protected $cachePath;

    /**
     * Create a new compiler instance.
     *
     * @param  string                      $cachePath
     * @throws \InvalidArgumentException
     * @return void
     */
    public function __construct($cachePath)
    {
        if (!$cachePath) {
            throw new InvalidArgumentException('Please provide a valid cache path.');
        }

        if (!is_dir($cachePath)) {
            @mkdir($cachePath, true);
        }

        $this->cachePath = $cachePath;
    }

    /**
     * Get the path to the compiled version of a view.
     *
     * @param  string   $path
     * @return string
     */
    public function getCompiledPath($path)
    {
        return $this->cachePath . '/' . sha1($path) . '.php';
    }

    /**
     * Determine if the view at the given path is expired.
     *
     * @param  string $path
     * @return bool
     */
    public function isExpired($path)
    {
        $compiled = $this->getCompiledPath($path);

        // If the compiled file doesn't exist we will indicate that the view is expired
        // so that it can be re-compiled. Else, we will verify the last modification
        // of the views is less than the modification times of the compiled views.
        if (!is_file($compiled)) {
            return true;
        }

        return filemtime($path) >= filemtime($compiled);
    }
}
