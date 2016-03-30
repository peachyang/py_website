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
     * @static
     * @var Cache
     */
    private static $instance = null;

    /**
     * @var CacheProvider
     */
    private $pool = null;

    /**
     * @param array $config
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
     * @param string $id
     * @return bool
     */
    public function contains($id)
    {
        return $this->pool->contains($id);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->pool->delete($id);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function fetch($id)
    {
        return unserialize(@gzinflate($this->pool->fetch($id)));
    }

    /**
     * @param string $id
     * @param mixed $data
     * @param int $lifeTime
     * @return bool
     */
    public function save($id, $data, $lifeTime = 0)
    {
        return $this->pool->save($id, gzdeflate(serialize($data)), $lifeTime);
    }

    /**
     * @param array $keys
     * @return array
     */
    public function fetchMultiple(array $keys)
    {
        $result = [];
        $values = $this->pool->fetchMultiple($keys);
        foreach ($values as $value) {
            $result[] = unserialize(@gzinflate($value));
        }
        return array_combine($keys, $result);
    }

    /**
     * @param array $keys
     * @param int $lifetime
     * @return bool
     */
    public function saveMultiple(array $keysAndValues, $lifetime = 0)
    {
        $pairs = [];
        foreach ($keysAndValues as $key => $value) {
            $pairs[$key] = gzdeflate(serialize($value));
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
