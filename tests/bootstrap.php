<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
umask(0);

define('DS', DIRECTORY_SEPARATOR);
define('BP', dirname(__DIR__) . DS);
require BP . 'vendor/autoload.php';

date_default_timezone_set('UTC');
$server = array(
    'REQUEST_METHOD' => 'GET',
    'SCRIPT_NAME' => '/index.php',
    'REQUEST_SCHEME' => 'http',
    'REQUEST_URI' => '/',
    'SERVER_PROTOCOL' => 'HTTP/1.1',
    'SERVER_NAME' => '127.0.0.1',
    'QUERY_STRING' => '',
    'HTTP_HOST' => '127.0.0.1',
    'HTTP_CONNECTION' => 'keep-alive',
    'HTTP_PRAGMA' => 'no-cache',
    'HTTP_CACHE_CONTROL' => 'no-cache',
    'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'HTTP_UPGRADE_INSECURE_REQUESTS' => 1,
    'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36',
    'HTTP_ACCEPT_ENCODING => gzip, deflate, sdch',
    'HTTP_ACCEPT_LANGUAGE' => 'zh-CN,zh;q=0.8'
);

\Seahinet\Lib\Bootstrap::init($server + $_SERVER);
$request = new \Seahinet\Lib\Http\Request($server + $_SERVER);
\Seahinet\Lib\Bootstrap::getContainer()['request'] = $request;
