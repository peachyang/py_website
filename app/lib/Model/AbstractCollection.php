<?php

namespace Seahinet\Lib\Model;

use BadMethodCallException;
use Exception;
use Zend\Db\Adapter\Exception\InvalidQueryException;
use Zend\Stdlib\ArrayObject;

/**
 * Data operator for collection model
 */
abstract class AbstractCollection extends ArrayObject
{

    use \Seahinet\Lib\Traits\DB;

    /**
     * @var \Zend\Db\Sql\Select 
     */
    protected $select = null;
    protected $cacheKey = '';
    protected $isLoaded = false;
    protected $eventDispatcher = null;
    protected $tableName = '';

    public function __construct($input = array(), $flags = self::ARRAY_AS_PROPS, $iteratorClass = 'ArrayIterator')
    {
        parent::__construct($input, $flags, $iteratorClass);
        $this->_construct();
    }

    /**
     * Overwrite normal method instead of magic method
     */
    abstract protected function _construct();

    public function __call($name, $arguments)
    {
        if (is_callable([$this->select, $name])) {
            call_user_func_array([$this->select, $name], $arguments);
            return $this;
        } else {
            throw new BadMethodCallException('Call to undefined method: ' . $name);
        }
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
        $this->select = $this->tableGateway->getSql()->select();
        $this->cacheKey = $table . '\\';
    }

    /**
     * Load data
     * 
     * @return AbstractCollection
     */
    public function load()
    {
        if (!$this->isLoaded) {
            try {
                $this->beforeLoadCache();
                $cache = $this->getContainer()->get('cache');
                $cacheKey = 'COLLECTION_DATA_' . $this->cacheKey . md5($this->select->getSqlString($this->tableGateway->getAdapter()->getPlatform()));
                $result = $cache->fetch($cacheKey);
                $this->afterLoadCache();
                if (!$result) {
                    $this->beforeLoad();
                    $result = $this->tableGateway->selectWith($this->select)->toArray();
                    if (count($result)) {
                        $this->storage = $result;
                        $this->afterLoad();
                        $cache->save($cacheKey, $result, 86400);
                    }
                } else {
                    $this->storage = $result;
                    $this->afterLoad();
                }
            } catch (InvalidQueryException $e) {
                $this->getContainer()->get('log')->logException($e);
            } catch (Exception $e) {
                $this->getContainer()->get('log')->logException($e);
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

}
