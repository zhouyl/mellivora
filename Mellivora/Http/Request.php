<?php

namespace Mellivora\Http;

use Slim\Http\Request as SlimHttpRequest;

class Request extends SlimHttpRequest
{

    public function isCli()
    {
        return PHP_SAPI === 'cli';
    }

}
