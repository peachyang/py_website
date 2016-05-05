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
 * @method Select reset(string $part)
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
    protected $cacheKey = '';
    protected $isLoaded = false;
    protected $eventDispatcher = null;
    protected $tableName = '';
    protected $primaryKey = '';

    public function __construct()
    {
        $this->_construct();
    }

    /**
     * Overwrite normal method instead of magic method
     */
    abstract protected function _construct();

    public function __call($name, $arguments)
    {
        if (is_callable([$this->select, $name])) {
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
    protected function init($table, $primaryKey = 'id')
    {
        $this->tableName = $table;
        $this->getTableGateway($table);
        $this->cacheKey = $table . '\\';
        $this->primaryKey = $primaryKey;
        if (is_null($this->select)) {
            $this->select = $this->tableGateway->getSql()->select();
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
                $this->beforeLoadCache();
                if ($useCache) {
                    $cacheKey = md5($this->select->getSqlString($this->tableGateway->getAdapter()->getPlatform()));
                    $result = $this->fetchList($cacheKey, $this->getCacheKey());
                } else {
                    $result = false;
                }
                $this->afterLoadCache();
                if (!$result) {
                    $this->beforeLoad();
                    $result = $this->tableGateway->selectWith($this->select)->toArray();
                    if (count($result)) {
                        $this->storage = $result;
                        $this->afterLoad();
                        if ($useCache) {
                            $this->addCacheList($cacheKey, $result, $this->getCacheKey());
                        }
                    }
                } else {
                    $this->storage = $result;
                    $this->afterLoad();
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

    protected function getEventDispatcher()
    {
        if (is_null($this->eventDispatcher)) {
            $this->eventDispatcher = $this->getContainer()->get('eventDispatcher');
        }
        return $this->eventDispatcher;
    }

    protected function beforeLoadCache()
    {
        $this->getEventDispatcher()->trigger(get_class($this) . '.collection.loadcache.before', ['collection' => $this]);
    }

    protected function afterLoadCache()
    {
        $this->getEventDispatcher()->trigger(get_class($this) . '.collection.loadcache.after', ['collection' => $this]);
    }

    protected function beforeLoad()
    {
        $this->getEventDispatcher()->trigger(get_class($this) . '.collection.load.before', ['collection' => $this]);
    }

    protected function afterLoad()
    {
        $this->isLoaded = true;
        $this->getEventDispatcher()->trigger(get_class($this) . '.collection.load.after', ['collection' => $this]);
    }

    public function getArrayCopy()
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        return parent::getArrayCopy();
    }
    
    public function &__get($key)
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        return parent::__get($key);
    }

    public function &offsetGet($key)
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        return parent::offsetGet($key);
    }

    public function getIterator()
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        return parent::getIterator();
    }

    public function offsetUnset($key)
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        return parent::offsetUnset($key);
    }

    public function __unset($key)
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        return parent::__unset($key);
    }

    public function count()
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        return parent::count();
    }

}
