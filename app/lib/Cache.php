<?php

namespace Seahinet\Lib;

use Doctrine\Common\Cache as DoctrineCache;
use Doctrine\Common\Cache\CacheProvider;
use Memcache;
use Memcached;
use MongoClient;
use MongoCollection;
use Predis\Client as Predis;
use Psr\Cache\CacheItemPoolInterface;
use Redis;
use Seahinet\Lib\Stdlib\Singleton;
use SQLite3;
use Zend\Stdlib\ArrayObject;

final class Cache extends ArrayObject implements CacheItemPoolInterface, Singleton
{

    /**
     * @var Cache
     */
    private static $instance = null;

    /**
     * @var CacheProvider
     */
    private $pool = null;

    /**
     * @var array
     */
    private $defer = [];
    public static $EXTENSION = [
        'apc' => ['version' => '3.0.0', 'name' => 'apc'],
        'apcu' => ['version' => '4.0.7', 'name' => 'apc'],
        'memcache' => ['version' => '2.0.0', 'name' => 'memcache'],
        'memcached' => ['version' => '2.0.0', 'name' => 'memcached'],
        'mongodb' => ['version' => '1.3.0', 'name' => 'mongo'],
        'predis' => ['version' => '2.2.7', 'name' => 'redis'],
        'redis' => ['version' => '2.2.7', 'name' => 'redis'],
        'wincache' => ['version' => '1.3.5.0', 'name' => 'wincache']
    ];

    /**
     * @param array|\ArrayAccess $config
     * @throws \UnexpectedValueException
     */
    private function __construct($config)
    {
        if (isset($config['adapter']) && $this->checkExtension($config['adapter'])) {
            $method = 'prepare' . $config['adapter'];
            $class = 'DoctrineCache\\' . $config['adapter'] . 'Cache';
            if (is_callable(array($this, $method))) {
                $this->$method($config);
            } else if (class_exists($class)) {
                $this->pool = new $class;
            } else {
                throw new \UnexpectedValueException('Bad adapter: ' . $config['adapter']);
            }
        } else {
            $this->prepareFilesystem($config);
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function instance($config = array())
    {
        if (is_null(static::$instance)) {
            static::$instance = new static($config);
        }
        return static::$instance;
    }

    /**
     * @param string $name
     * @return boolean
     */
    private function checkExtension($name)
    {
        if (isset(static::$EXTENSION_VERSION[strtolower($name)])) {
            return extension_loaded(static::$EXTENSION_VERSION[strtolower($name)]['name']) && version_compare(phpversion(static::$EXTENSION_VERSION[strtolower($name)]['name']), static::$EXTENSION_VERSION[strtolower('version')], '>=');
        }
        return true;
    }

    /**
     * @param array|\ArrayAccess $config
     */
    private function prepareMemcache($config)
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
        $this->pool = new DoctrineCache\MemcacheCache();
        $this->pool->setMemcache($client);
    }

    /**
     * @param array|\ArrayAccess $config
     */
    private function prepareMemcached($config)
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
        $this->pool = new DoctrineCache\MemcachedCache();
        $this->pool->setMemcache($client);
    }

    /**
     * @param array|\ArrayAccess $config
     */
    private function prepareRedis($config)
    {
        $client = new Redis;
        $client->connect((isset($config['host']) ? $config['host'] : 'localhost'), (isset($config['port']) ? $config['port'] : 6379));
        $this->pool = new DoctrineCache\RedisCache();
        $this->pool->setRedis($client);
    }

    /**
     * @param array|\ArrayAccess $config
     */
    private function preparePredis($config)
    {
        $server = [
            'scheme' => isset($config['scheme']) ? $config['scheme'] : 'tcp',
            'host' => isset($config['host']) ? $config['host'] : '127.0.0.1',
            'port' => isset($config['port']) ? $config['port'] : 6379,
        ];
        $client = new Predis($server);
        $this->pool = new DoctrineCache\PredisCache($client);
        $this->pool->setRedis($client);
    }

    /**
     * @param array|\ArrayAccess $config
     */
    private function prepareFilesystem($config)
    {
        $this->pool = new DoctrineCache\FilesystemCache((isset($config['directory']) ? $config['directory'] : BP . 'var/cache'), '.dat', 0077);
    }

    /**
     * @param array|\ArrayAccess $config
     */
    private function preparePHPFile($config)
    {
        $this->pool = new DoctrineCache\PHPFileCache((isset($config['directory']) ? $config['directory'] : BP . 'var/cache'), '.php', 0077);
    }

    /**
     * @param array|\ArrayAccess $config
     */
    private function prepareMongoDB($config)
    {
        $server = 'mongodb://localhost:27017';
        if (isset($config['server'])) {
            $server = $config['server'];
            unset($collection['server']);
        } else if (isset($collection['host'])) {
            $server = 'mongodb://' . $config['host'] . (isset($config['port']) ? $config['port'] : 27017);
            unset($config['host']);
            unset($config['port']);
        }
        $db = isset($config['db']) ? $config['db'] : 'seahinet';
        unset($config['db']);
        $client = new MongoClient($server, $config);
        $collection = new MongoCollection($client->selectDB($db), 'cache');
        $this->pool = new DoctrineCache\MongoDBCache($collection);
    }

    /**
     * @param array|\ArrayAccess $config
     */
    private function prepareSQLite3($config)
    {
        $client = new SQLite3(isset($config['filename']) ? $config['filename'] : BP . 'var/cache/sqlite3.dat');
        $this->pool = new DoctrineCache\SQLite3Cache($client, 'cache');
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        return $this->pool->deleteAll();
    }

    /**
     * {@inheritDoc}
     */
    public function commit()
    {
        $result = $this->pool->saveMultiple($this->defer);
        $this->defer = [];
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteItem($key)
    {
        unset($this->storage[$key]);
        return $this->pool->delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteItems(array $keys)
    {
        $result = 1;
        foreach ($keys as $key) {
            $result &= $this->deleteItem($key);
        }
        return (bool) $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getItem($key)
    {
        if (!isset($this->storage[$key])) {
            $this->storage[$key] = $this->pool->fetch($key);
        }
        return $this->storage[$key];
    }

    /**
     * {@inheritDoc}
     */
    public function getItems(array $keys = array())
    {
        $result = $this->pool->fetchMultiple($keys);
        $this->storage = array_merge($this->storage, $result);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function hasItem($key)
    {
        return isset($this->storage[$key]) || $this->pool->contains($key);
    }

    /**
     * {@inheritDoc}
     */
    public function save($key, $data)
    {
        return $this->pool->save($key, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function saveDeferred($key, $data)
    {
        $this->storage[$key] = $data;
        $this->defer[$key] = $data;
        return true;
    }

}
