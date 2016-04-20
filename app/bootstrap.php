<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
umask(0);

if (version_compare(phpversion(), '5.5.0', '<') === true) {
    echo 'SeaHiNet E-Commerce Suite v2 supports PHP 5.5.0 or later. (Current: ', phpversion(), ')';
    exit(1);
}

define('DS', DIRECTORY_SEPARATOR);
define('BP', dirname(__DIR__) . DS);
var_dump(BP);
require BP . 'vendor/autoload.php';

date_default_timezone_set('UTC');
