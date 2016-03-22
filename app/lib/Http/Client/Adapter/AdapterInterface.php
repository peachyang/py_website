<?php

namespace Seahinet\Lib\Http\Client\Adapter;

/**
 * @see https://github.com/zendframework/zend-http/blob/master/src/Client/Adapter/AdapterInterface.php
 */
interface AdapterInterface
{

    /**
     * @param array $options
     */
    public function setOptions($options = []);

    /**
     * @param string  $host
     * @param int     $port
     * @param  bool $secure
     */
    public function connect($host, $port = 80, $secure = false);

    /**
     * @param string        $method
     * @param \Zend\Uri\Uri $url
     * @param string        $httpVer
     * @param array         $headers
     * @param string        $body
     * @return string
     */
    public function write($method, $url, $httpVer = '1.1', $headers = [], $body = '');

    /**
     * @return string
     */
    public function read();

    public function close();
}
