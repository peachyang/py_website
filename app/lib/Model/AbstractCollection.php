<?php

namespace Seahinet\Lib\Model;

use Exception;
use Zend\Db\Adapter\Exception\InvalidQueryException;
use Zend\Stdlib\ArrayObject;

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
    protected function load()
    {
        if (!$this->isLoaded) {
            try {
                $cache = $this->getContainer()->get('cache');
                $cacheKey = 'COLLECTION_DATA_' . $this->cacheKey . md5($this->select->getSqlString($this->tableGateway->getAdapter()->getPlatform()));
                $result = $cache->fetch($cacheKey);
                if (!$result) {
                    $this->beforeLoad();
                    $result = $this->tableGateway->selectWith($this->select)->toArray();
                    if (count($result)) {
                        $this->storage = $result;
                        $this->afterLoad();
                        $cache->save($cacheKey, $result);
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
