<?php

namespace Seahinet\Lib\Model;

use BadMethodCallException;
use Exception;
use Zend\Db\Adapter\Exception\InvalidQueryException;
use Zend\Db\Sql;
use Zend\Db\Sql\Select;
use Seahinet\Lib\Stdlib\ArrayObject;

/**
 * Data operator for collection model
 * 
 * @uses Select
 * @method Select columns(array $columns, bool  $prefixColumnsWithTable)
 * @method Select join(string|array $name, string $on, string|array $columns, string $type)
 * @method Select where(Sql\Where|\Closure|string|array|Sql\Predicate\PredicateInterface $predicate, string $combination)
 * @method Select group(array|string $group)
 * @method Select having(Sql\Where|\Closure|string|array $predicate, , string $combination)
 * @method Select order(string|array $order)
 * @method Select limit(int $limit)
 * @method Select offset(int $offset)
 * @method Select combine(Select $select, string $type, string $modifier)
 * @method string getRawState(null|string $key)
 * @method string getSqlString(\Zend\Db\Adapter\Platform\PlatformInterface $adapterPlatform)
 */
abstract class AbstractCollection extends ArrayObject
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\DB,
        \Seahinet\Lib\Traits\DataCache;

    /**
     * @var Select 
     */
    protected $select = null;

    /**
     * @var \Seahinet\Lib\EventDispatcher 
     */
    protected $eventDispatcher = null;
    protected $cacheKey = '';
    protected $isLoaded = false;
    protected $arrayMode = false;
    protected $tableName = '';

    public function __construct()
    {
        $this->construct();
    }

    /**
     * Overwrite normal method instead of magic method
     */
    abstract protected function construct();

    public function __call($name, $arguments)
    {
        if (is_callable([$this->select, $name])) {
            $this->isLoaded = false;
            return call_user_func_array([$this->select, $name], $arguments);
        } else {
            throw new BadMethodCallException('Call to undefined method: ' . $name);
        }
    }

    public function __clone()
    {
        $this->select = clone $this->select;
        $this->isLoaded = false;
    }

    /**
     * Data operator initialization
     * 
     * @param string $table
     */
    protected function init($table)
    {
        $this->tableName = $table;
        $this->getTableGateway($table);
        $this->cacheKey = $table;
        if (is_null($this->select)) {
            $this->select = $this->getTableGateway($this->tableName)->getSql()->select();
        }
    }

    /**
     * Get cache key
     * 
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * Load data
     * 
     * @return AbstractCollection
     */
    public function load($useCache = true)
    {
        if (!$this->isLoaded) {
            try {
                if ($useCache) {
                    $cacheKey = md5($this->select->getSqlString($this->getTableGateway($this->tableName)->getAdapter()->getPlatform()));
                    $result = $this->fetchList($cacheKey, $this->getCacheKey());
                } else {
                    $result = false;
                }
                if (!$result) {
                    $this->beforeLoad();
                    $result = $this->getTableGateway($this->tableName)->selectWith($this->select)->toArray();
                    if (count($result)) {
                        $this->afterLoad($result);
                        if ($useCache) {
                            $this->addCacheList($cacheKey, $result, $this->getCacheKey());
                        }
                    }
                } else {
                    $this->afterLoad($result);
                }
            } catch (InvalidQueryException $e) {
                $this->getContainer()->get('log')->logException($e);
                throw $e;
            } catch (Exception $e) {
                $this->getContainer()->get('log')->logException($e);
                throw $e;
            }
        }
        return $this;
    }

    /**
     * Walk collection
     * 
     * @param callable $callback
     */
    public function walk($callback, ...$params)
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        array_walk($this->storage, $callback, $params);
    }

    /**
     * Get event dispatcher object
     * 
     * @return \Seahinet\Lib\EventDispatcher
     */
    protected function getEventDispatcher()
    {
        if (is_null($this->eventDispatcher)) {
            $this->eventDispatcher = $this->getContainer()->get('eventDispatcher');
        }
        return $this->eventDispatcher;
    }

    /**
     * Event before load data
     */
    protected function beforeLoad()
    {
        $this->getEventDispatcher()->trigger(get_class($this) . '.collection.load.before', ['collection' => $this]);
    }

    /**
     * Event after load cache
     */
    protected function afterLoad(&$result)
    {
        $this->isLoaded = true;
        $className = str_replace('\\Collection', '', get_class($this));
        if (!$this->arrayMode && class_exists($className)) {
            foreach ($result as &$item) {
                if (is_array($item)) {
                    $object = new $className;
                    $object->setData($item);
                    $item = $object;
                } else {
                    break;
                }
            }
        }
        $this->storage = $result;
        $this->getEventDispatcher()->trigger(get_class($this) . '.collection.load.after', ['collection' => $this]);
    }

    public function serialize()
    {
        $storage = $this->storage;
        if (!$this->arrayMode) {
            foreach ($this->storage as &$item) {
                if (is_object($item)) {
                    $item = $item->toArray();
                } else {
                    break;
                }
            }
        }
        $result = parent::serialize();
        $this->storage = $storage;
        return $result;
    }

    public function unserialize($data)
    {
        $data = unserialize($data);
        foreach ($data as $key => $value) {
            if ($key === 'storage' && !$this->arrayMode) {
                $className = str_replace('\\Collection', '', get_class($this));
                if (class_exists($className)) {
                    foreach ($value as &$item) {
                        if (is_array($item)) {
                            $object = new $className;
                            $object->setData($item);
                            $item = $object;
                        } else {
                            break;
                        }
                    }
                }
            } else {
                $this->$key = $value;
            }
        }
        if ($this instanceof Singleton) {
            static::$instance = $this;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getArrayCopy()
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        return parent::getArrayCopy();
    }

    /**
     * {@inheritdoc}
     */
    public function &__get($key)
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        return parent::__get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function &offsetGet($key)
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        return parent::offsetGet($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        return parent::getIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($key)
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        return parent::offsetUnset($key);
    }

    /**
     * {@inheritdoc}
     */
    public function __unset($key)
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        return parent::__unset($key);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        return parent::count();
    }

    /**
     * Reset part of select object
     * 
     * @param string $part
     * @return Select
     */
    public function reset($part)
    {
        $this->isLoaded = false;
        $this->storage = [];
        return $this->select->reset($part);
    }

}
