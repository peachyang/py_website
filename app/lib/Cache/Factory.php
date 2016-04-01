<?php

namespace Seahinet\Lib\Cache;

use Doctrine\Common\Cache as DoctrineCache;
use Memcache;
use Memcached;
use MongoClient;
use MongoCollection;
use MongoDB\Driver\Manager as MongoDBManager;
use MongoDB\Collection as MongoDBCollection;
use Predis\Client as Predis;
use Redis;
use SQLite3;

/**
 * Generate cache pool based on config array
 */
abstract class Factory
{

    /**
     * @var array Describe the minimal version of php extension 
     */
    public static $EXTENSION_VERSION = [
        'apc' => ['version' => '3.0.0', 'name' => 'apc'], // deprecated
        'apcu' => ['version' => '4.0.7', 'name' => 'apcu'],
        'memcache' => ['version' => '2.0.0', 'name' => 'memcache'],
        'memcached' => ['version' => '2.0.0', 'name' => 'memcached'],
        'mongo' => ['version' => '1.3.0', 'name' => 'mongo'], // deprecated
        'mongodb' => ['version' => '1.1.0', 'name' => 'mongodb'],
        'predis' => ['version' => '2.2.7', 'name' => 'redis'],
        'redis' => ['version' => '2.2.7', 'name' => 'redis'],
        'wincache' => ['version' => '1.3.5.0', 'name' => 'wincache']
    ];

    /**
     * @param array $config
     * @return mixed
     * @throws \UnexpectedValueException
     */
    public static function getCachePool($config)
    {
        try {
            if (isset($config['adapter']) && static::checkExtension($config['adapter'])) {
                $method = 'prepare' . $config['adapter'];
                $class = 'Doctrine\\Common\\Cache\\' . $config['adapter'] . 'Cache';
                if (is_callable('\\Seahinet\\Lib\\Cache\\Factory::' . $method)) {
                    return static::$method($config);
                } else if (class_exists($class)) {
                    return new $class;
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return static::prepareFilesystem($config);
    }

    /**
     * @param string $name
     * @return boolean
     */
    private static function checkExtension($name)
    {
        $name = strtolower($name);
        if (isset(static::$EXTENSION_VERSION[$name])) {
            return extension_loaded(static::$EXTENSION_VERSION[$name]['name']) && version_compare(phpversion(static::$EXTENSION_VERSION[$name]['name']), static::$EXTENSION_VERSION[$name]['version'], '>=');
        }
        return true;
    }

    /**
     * @param array|\ArrayAccess $config
     */
    private static function prepareMemcache($config)
    {
        $client = new Memcache;
        if (isset($config['host'])) {
            $host = $config['host'];
            $port = 11211;
        } else if (isset($config['socket'])) {
            $host = $config['socket'];
            $port = 0;
        }
        $client->addServer($host, $port);
        $pool = new DoctrineCache\MemcacheCache();
        $pool->setMemcache($client);
        return $pool;
    }

    /**
     * @param array|\ArrayAccess $config
     */
    private static function prepareMemcached($config)
    {
        $client = new Memcached;
        if (isset($config['host'])) {
            $host = $client['host'];
            $port = 11211;
        } else if (isset($config['socket'])) {
            $host = $config['socket'];
            $port = 0;
        }
        $client->addServer($host, $port);
        $pool = new DoctrineCache\MemcachedCache();
        $pool->setMemcache($client);
        return $pool;
    }

    /**
     * @param array|\ArrayAccess $config
     */
    private static function prepareRedis($config)
    {
        $client = new Redis;
        $client->connect((isset($config['host']) ? $config['host'] : 'localhost'), (isset($config['port']) ? $config['port'] : 6379));
        $pool = new DoctrineCache\RedisCache();
        $pool->setRedis($client);
        return $pool;
    }

    /**
     * @param array|\ArrayAccess $config
     */
    private static function preparePredis($config)
    {
        $server = [
            'scheme' => isset($config['scheme']) ? $config['scheme'] : 'tcp',
            'host' => isset($config['host']) ? $config['host'] : '127.0.0.1',
            'port' => isset($config['port']) ? $config['port'] : 6379,
        ];
        $client = new Predis($server);
        $pool = new DoctrineCache\PredisCache($client);
        $pool->setRedis($client);
        return $pool;
    }

    /**
     * @param array|\ArrayAccess $config
     */
    private static function prepareFilesystem($config)
    {
        $pool = new DoctrineCache\FilesystemCache((isset($config['directory']) ? $config['directory'] : BP . 'var/cache'), '.dat', 0077);
        return $pool;
    }

    /**
     * @param array|\ArrayAccess $config
     */
    private static function preparePHPFile($config)
    {
        $pool = new DoctrineCache\PHPFileCache((isset($config['directory']) ? $config['directory'] : BP . 'var/cache'), '.php', 0077);
        return $pool;
    }

    /**
     * @param array|\ArrayAccess $config
     */
    private static function prepareMongo($config)
    {
        $server = 'mongodb://localhost:27017';
        if (isset($config['server'])) {
            $server = $config['server'];
            unset($config['server']);
        } else if (isset($config['host'])) {
            $server = 'mongodb://' . $config['host'] . (isset($config['port']) ? $config['port'] : 27017);
            unset($config['host']);
            unset($config['port']);
        }
        $db = isset($config['db']) ? $config['db'] : 'seahinet';
        unset($config['db']);
        $client = new MongoClient($server, $config);
        $collection = new MongoCollection($client->selectDB($db), 'cache');
        $pool = new DoctrineCache\MongoDBCache($collection);
        return $pool;
    }

    /**
     * @param array|\ArrayAccess $config
     */
    private static function prepareMongoDB($config)
    {
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
        $collection = new MongoDBCollection($manager, $db, 'cache');
        $pool = new MongoDBCache($collection);
        return $pool;
    }

    /**
     * @param array|\ArrayAccess $config
     */
    private static function prepareSQLite3($config)
    {
        $client = new SQLite3(isset($config['filename']) ? $config['filename'] : BP . 'var/cache/sqlite3.dat');
        $pool = new DoctrineCache\SQLite3Cache($client, 'cache');
        return $pool;
    }

}
