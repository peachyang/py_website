<?php

namespace Seahinet\Lib;

use ArrayAccess;
use BadMethodCallException;
use Doctrine\Common\Cache\CacheProvider;
use Seahinet\Lib\Stdlib\Singleton;

/**
 * Handle cache operation by using Doctrine/Cache pool
 * 
 * @method array|null getStats()
 * @method bool flushAll()
 * @method bool deleteAll()
 */
final class Cache implements ArrayAccess, Singleton
{

    use Traits\Container;

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
    private $unhitPrefix = [];

    /**
     * @param array|Container $config
     * @throws \UnexpectedValueException
     */
    private function __construct($config = [])
    {
        if ($config instanceof Container) {
            $this->setContainer($config);
            $config = [];
        }
        if (empty($config)) {
            $config = $this->getContainer()->get('config')['adapter']['cache'];
        }
        $this->pool = Cache\Factory::getCachePool($config);
    }

    /**
     * Call pool method
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, $arguments)
    {
        if (is_callable([$this->pool, $name])) {
            return call_user_func_array([$this->pool, $name], $arguments);
        } else {
            throw new BadMethodCallException('Call to undefined method: ' . $name);
        }
    }

    /**
     * @param array $config
     * @return Cache
     */
    public static function instance($config = [])
    {
        if (is_null(static::$instance)) {
            static::$instance = new static($config);
        }
        return static::$instance;
    }

    /**
     * @uses CacheProvider::contains
     * @param string $id
     * @return bool
     */
    public function contains($id)
    {
        return $this->pool->contains($id);
    }

    /**
     * @uses CacheProvider::delete
     * @param string $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->pool->delete($id);
    }

    /**
     * @uses CacheProvider::fetch
     * @param string $id
     * @return mixed
     */
    public function fetch($id)
    {
        if (count($this->unhitPrefix) && preg_match('/^(' . implode('|', $this->unhitPrefix) . ')/', $id)) {
            return null;
        }
        return unserialize(@gzdecode($this->pool->fetch($id)));
    }

    /**
     * Set prefix of unhit cache key
     * 
     * @param string $prefix
     * @return Cache
     */
    public function unhit($prefix)
    {
        $this->unhitPrefix[] = $prefix;
        return $this;
    }

    /**
     * @uses CacheProvider::save
     * @param string $id
     * @param mixed $data
     * @param int $lifeTime
     * @return bool
     */
    public function save($id, $data, $lifeTime = 0)
    {
        return $this->pool->save($id, gzencode(serialize($data)), $lifeTime);
    }

    /**
     * @uses CacheProvider::fetchMultiple
     * @param array $keys
     * @return array
     */
    public function fetchMultiple(array $keys)
    {
        $result = [];
        $fetchKey = $keys;
        if (count($this->unhitPrefix)) {
            $fetchKey = [];
            $regex = '/^(' . implode('|', $this->unhitPrefix) . ')/';
            foreach ($keys as $key) {
                if (!preg_match($regex, $key)) {
                    $fetchKey[] = $key;
                }
            }
        }
        $values = $this->pool->fetchMultiple($fetchKey);
        foreach ($values as $value) {
            $result[] = unserialize(@gzdecode($value));
        }
        return array_combine($fetchKey, $result);
    }

    /**
     * @uses CacheProvider::saveMultiple
     * @param array $keys
     * @param int $lifetime
     * @return bool
     */
    public function saveMultiple(array $keysAndValues, $lifetime = 0)
    {
        $pairs = [];
        foreach ($keysAndValues as $key => $value) {
            $pairs[$key] = gzencode(serialize($value));
        }
        return $this->pool->saveMultiple($pairs, $lifetime);
    }

    public function offsetExists($offset)
    {
        return $this->contains($offset);
    }

    public function offsetGet($offset)
    {
        return $this->fetch($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->save($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->delete($offset);
    }

}
