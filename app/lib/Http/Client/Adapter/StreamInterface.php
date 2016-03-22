<?php

namespace Seahinet\Lib\Http\Client\Adapter;

/**
 * @see https://github.com/zendframework/zend-http/blob/master/src/Client/Adapter/StreamInterface.php
 */
interface StreamInterface
{

    /**
     * @param resource $stream
     */
    public function setOutputStream($stream);
}
