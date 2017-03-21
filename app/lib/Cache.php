<?php

namespace Seahinet\Lib;

use ArrayAccess;
use BadMethodCallException;
use Exception;
use Doctrine\Common\Cache\CacheProvider;
use Seahinet\Lib\Cache\Factory;
use Seahinet\Lib\Stdlib\Singleton;
use SoapClient;

/**
 * Handle cache operation by using Doctrine\Cache pool
 * 
 * @method array|null getStats()
 * @method bool flushAll()
 * @method bool deleteAll()
 */
final class Cache implements ArrayAccess, Singleton
{

    use \Seahinet\Lib\Traits\Container;

    /**
     * @var Cache
     */
    protected static $instance = null;

    /**
     * @var CacheProvider
     */
    private $pool = null;

    /**
     * @var array
     */
    private $unhitPrefix = [];

    /**
     * @var array
     */
    private $persistentPrefix = [];

    /**
     * @var string
     */
    public $salt = '';

    /**
     * @var callable
     */
    private $decoder;

    /**
     * @var callable
     */
    private $encoder;

    /**
     * @var bool
     */
    private $disabled;

    /**
     * @var array
     */
    private $remote = [];

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
        $this->pool = Factory::getCachePool($config);
        if (isset($config['persistent'])) {
            $this->persistentPrefix = (array) $config['persistent'];
        }
        if (isset($config['salt'])) {
            $this->salt = $config['salt'];
        }
        if (isset($config['remote'])) {
            $this->remote = (array) $config['remote'];
        }
        $this->disabled = (bool) ($config['disabled'] ?? false);
        if (!isset($config['compress']) || $config['compress']) {
            $this->encoder = function ($data) {
                return gzencode(serialize($data));
            };
            $this->decoder = function ($data) {
                return unserialize(@gzdecode($data));
            };
        } else {
            $this->encoder = function ($data) {
                return serialize($data);
            };
            $this->decoder = function ($data) {
                return unserialize($data);
            };
        }
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
     * @param string $prefix
     * @return bool
     */
    public function delete($id = '', $prefix = '')
    {
        if ($prefix) {
            if (in_array($prefix, $this->persistentPrefix)) {
                return false;
            }
            $list = $this->pool->fetch($this->salt . 'CACHE_LIST_' . $prefix);
            if ($list) {
                $list = call_user_func($this->decoder, $list);
                unset($list[$id]);
            } else {
                $list = [];
            }
            $this->pool->save($this->salt . 'CACHE_LIST_' . $prefix, call_user_func($this->encoder, $list));
            if (empty($list)) {
                $list = $this->pool->fetch($this->salt . 'CACHE_LIST');
                if ($list) {
                    $list = call_user_func($this->decoder, $list);
                    unset($list[$prefix]);
                } else {
                    $list = [];
                }
                $this->pool->save($this->salt . 'CACHE_LIST', call_user_func($this->encoder, $list));
            } else if (!$id) {
                foreach ($list as $key => $value) {
                    $this->pool->delete($this->salt . $prefix . $key);
                }
            }
        }
        $result = $id ? $this->pool->delete($this->salt . $prefix . $id) : true;
        try {
            $data = json_encode([
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'flushCache',
                'params' => [$id, $prefix]
            ]);
            foreach ($this->remote as $client) {
                $client = curl_init($client);
                curl_setopt($client, CURLOPT_POST, 1);
                curl_setopt($client, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Content-Length: ' . strlen($data)
                ]);
                curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($client, CURLOPT_POSTFIELDS, $data);
                curl_exec($client);
                curl_close($client);
            }
        } catch (Exception $e) {
            
        }
        return $result;
    }

    /**
     * @uses CacheProvider::fetch
     * @param string $id
     * @param string $prefix
     * @return mixed
     */
    public function fetch($id, $prefix = '')
    {
        if ($this->disabled || count($this->unhitPrefix) && in_array($prefix, $this->unhitPrefix)) {
            $result = null;
        } else {
            $result = call_user_func($this->decoder, $this->pool->fetch($this->salt . $prefix . $id));
        }
        if (Bootstrap::isDeveloperMode()) {
            $this->getContainer()->get('eventDispatcher')->trigger('cache.fetch.after', ['key' => $id, 'prefix' => $prefix, 'result' => $result]);
        }
        return $result;
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
     * @param string $prefix
     * @param int $lifeTime
     * @return bool
     */
    public function save($id, $data, $prefix = '', $lifeTime = 0)
    {
        if ($this->disabled) {
            return true;
        }
        if ($prefix) {
            $list = $this->pool->fetch($this->salt . 'CACHE_LIST_' . $prefix);
            if ($list) {
                $list = call_user_func($this->decoder, $list);
            } else {
                $list = [];
            }
            if (!isset($list[$id])) {
                $list[$id] = 1;
                $this->pool->save($this->salt . 'CACHE_LIST_' . $prefix, call_user_func($this->encoder, $list));
            }
            $list = $this->pool->fetch($this->salt . 'CACHE_LIST');
            if ($list) {
                $list = call_user_func($this->decoder, $list);
            } else {
                $list = [];
            }
            if (!isset($list[$prefix])) {
                $list[$prefix] = 1;
                $this->pool->save($this->salt . 'CACHE_LIST', call_user_func($this->encoder, $list));
            }
        }
        return $this->pool->save($this->salt . $prefix . $id, call_user_func($this->encoder, $data), $lifeTime);
    }

    /**
     * @uses CacheProvider::fetchMultiple
     * @param array $keys
     * @return array
     */
    public function fetchMultiple(array $keys, $prefix = '')
    {
        $result = [];
        if (!$this->disabled) {
            $fetchKey = $keys;
            if (count($this->unhitPrefix)) {
                $fetchKey = [];
                $regex = '/^(' . implode('|', $this->unhitPrefix) . ')/';
                foreach ($keys as $key) {
                    if (!preg_match($regex, $key)) {
                        $fetchKey[] = $this->salt . $key;
                    }
                }
            }
            $values = $this->pool->fetchMultiple($fetchKey);
            foreach ($values as $value) {
                $result[] = call_user_func($this->decoder, $value);
            }
            $result = array_combine($fetchKey, $result);
        }
        if (Bootstrap::isDeveloperMode()) {
            $this->getContainer()->get('eventDispatcher')->trigger('cache.fetchMultiple.after', ['keys' => $keys, 'prefix' => $prefix, 'result' => $result]);
        }
        return $result;
    }

    /**
     * @uses CacheProvider::saveMultiple
     * @param array $keys
     * @param int $lifetime
     * @return bool
     */
    public function saveMultiple(array $keysAndValues, $lifetime = 0)
    {
        if ($this->disabled) {
            return true;
        }
        $pairs = [];
        foreach ($keysAndValues as $key => $value) {
            $pairs[$this->salt . $key] = call_user_func($this->encoder, $value);
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
