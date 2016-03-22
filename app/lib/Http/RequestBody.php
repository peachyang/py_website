<?php

namespace Seahinet\Lib\Http;

/**
 * @see https://github.com/slimphp/Slim/blob/3.x/Slim/Http/RequestBody.php
 */
class RequestBody extends Body
{

    public function __construct()
    {
        $stream = fopen('php://temp', 'w+');
        stream_copy_to_stream(fopen('php://input', 'r'), $stream);
        rewind($stream);

        parent::__construct($stream);
    }

}
