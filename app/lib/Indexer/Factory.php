<?php

namespace Seahinet\Lib\Indexer;

use MongoDB\Driver\Manager as MongoDBManager;

abstract class Factory
{

    public static function getHandler($config, $entityType)
    {
        try {
            if (isset($config['adapter'])) {
                $method = 'prepare' . $config['adapter'];
                if (is_callable(__CLASS__ . '::' . $method)) {
                    $handler = static::$method($config, $entityType);
                }
            }
            if (empty($handler)) {
                $handler = static::prepareDatabase($entityType);
            }
            return $handler;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return null;
    }

    private static function prepareDatabase($entityType)
    {
        return new Handler\Database($entityType);
    }

    private static function prepareMongoDB($config, $entityType)
    {
        if (extension_loaded('mongodb') && version_compare(phpversion('mongodb'), '1.1.0', '>=')) {
            $server = 'mongodb://localhost:27017';
            if (isset($config['server'])) {
                $server = $config['server'];
                unset($config['server']);
            } else {
                if (isset($config['username'])) {
                    $auth = $config['username'] . (isset($config['password']) ? ':' . $config['password'] : '') . '@';
                    unset($config['username']);
                    unset($config['password']);
                } else if (isset($config['user'])) {
                    $auth = $config['user'] . (isset($config['pass']) ? ':' . $config['pass'] : '') . '@';
                    unset($config['user']);
                    unset($config['pass']);
                } else {
                    $auth = '';
                }
                if (isset($config['host'])) {
                    $server = 'mongodb://' . $auth . $config['host'] . (isset($config['port']) ? $config['port'] : 27017);
                    unset($config['host']);
                    unset($config['port']);
                } else if (isset($config['socket'])) {
                    $server = 'mongodb://' . $auth . $config['socket'];
                    unset($config['socket']);
                }
            }
            $db = isset($config['db']) ? $config['db'] : 'seahinet';
            unset($config['db']);
            $manager = new MongoDBManager($server, $config);
            return new Handler\MongoDB($manager, $db, $entityType);
        }
        return false;
    }

}
