<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
umask(0);

define('DS', DIRECTORY_SEPARATOR);
define('BP', dirname(dirname(dirname(__DIR__))) . DS);
require BP . 'vendor/autoload.php';

date_default_timezone_set('UTC');
$server = array(
    "SCRIPT_FILENAME" => "/home/html/ecomv2admin/index.php",
    "REQUEST_METHOD" => "GET",
    "SCRIPT_NAME" => "/index.php",
    "REQUEST_URI" => "/test.html",
    "DOCUMENT_URI" => "/index.php",
    "DOCUMENT_ROOT" => "/home/html/ecomv2admin",
    "SERVER_PROTOCOL" => "HTTP/1.1",
    "GATEWAY_INTERFACE" => "CGI/1.1",
    "SERVER_SOFTWARE" => "nginx/1.8.1",
    "SERVER_NAME" => "ecomv2.lh.com",
    "REDIRECT_STATUS" => "200",
    "HTTP_HOST" => "ecomv2.lh.com",
    "QUERY_STRING" => "",
    "PHP_SELF" => "/index.php");

\Seahinet\Lib\Bootstrap::init($server);
$request = new \Seahinet\Lib\Http\Request($server);
\Seahinet\Lib\Bootstrap::getContainer()['request'] = $request;
