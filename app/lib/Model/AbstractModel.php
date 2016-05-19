<?php

namespace Seahinet\Lib\Model;

use Exception;
use Seahinet\Lib\Stdlib\ArrayObject;
use Zend\Db\Adapter\Exception\InvalidQueryException;

/**
 * Data operator for single model
 */
abstract class AbstractModel extends ArrayObject
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\DB,
        \Seahinet\Lib\Traits\DataCache;

    protected $columns = [];
    protected $updatedColumns = [];
    protected $primaryKey = 'id';
    protected $isNew = true;
    protected $isLoaded = false;
    protected $cacheKey = '';
    protected $eventDispatcher = null;
    protected $tableName = '';

    public function __construct($input = array())
    {
        $this->storage = $input;
        $this->construct();
    }

    /**
     * Overwrite normal method instead of magic method
     */
    abstract protected function construct();

    /**
     * Data operator initialization
     * 
     * @param string $table         Table name
     * @param string $primaryKey    Primary key name
     * @param array $columns        Table columns
     */
    protected function init($table, $primaryKey = 'id', $columns = [])
    {
        $this->tableName = $table;
        $this->getTableGateway($table);
        $this->cacheKey = $table;
        $this->columns = $columns;
        $this->primaryKey = $primaryKey;
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
     * Get primary key value
     * 
     * @return int|string
     */
    public function getId()
    {
        return isset($this->storage[$this->primaryKey]) ? $this->storage[$this->primaryKey] : null;
    }

    /**
     * Set primary key value
     * 
     * @param int|string $id
     * @return AbstractModel
     */
    public function setId($id)
    {
        if (is_null($id)) {
            $this->isNew = true;
        }
        $this->storage[$this->primaryKey] = $id;
        return $this;
    }

    public function offsetSet($key, $value)
    {
        $this->updatedColumns[] = $key;
        parent::offsetSet($key, $value);
    }

    public function offsetUnset($key)
    {
        $this->updatedColumns[] = $key;
        parent::offsetUnset($key);
    }

    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->offsetSet($k, $v);
            }
        } else {
            $this->offsetSet($key, $value);
        }
        return $this;
    }

    /**
     * Load data
     * 
     * @param int|string $id    Primary key value by default
     * @param string $key
     * @return AbstractModel
     */
    public function load($id, $key = null)
    {
        if (!$this->isLoaded) {
            try {
                if (is_null($key) || $key === $this->primaryKey) {
                    $key = $this->primaryKey;
                    $result = $this->fetchRow($id, null, $this->getCacheKey());
                } else {
                    $result = $this->fetchRow($id, $key, $this->getCacheKey());
                }
                if (!$result) {
                    $select = $this->getTableGateway($this->tableName)->getSql()->select();
                    $select->where([$this->tableName . '.' . $key => $id]);
                    $this->beforeLoad($select);
                    $result = $this->getTableGateway($this->tableName)->selectWith($select)->toArray();
                    if (count($result)) {
                        $this->afterLoad($result);
                        $this->flushRow($this->storage[$this->primaryKey], $this->storage, $this->getCacheKey());
                        if ($key !== $this->primaryKey) {
                            $this->addCacheAlias($key . '=' . $id, $this->storage[$this->primaryKey], $this->getCacheKey());
                        }
                    }
                } else {
                    $this->afterLoad($result);
                }
            } catch (InvalidQueryException $e) {
                $this->getContainer()->get('log')->logException($e);
                if ($this->transaction) {
                    $this->rollback();
                }
                throw $e;
            } catch (Exception $e) {
                $this->getContainer()->get('log')->logException($e);
                throw $e;
            }
        }
        return $this;
    }

    /**
     * Insert/Update data
     * 
     * @param array $constraint     Update query constraint
     * @return AbstractModel
     */
    public function save($constraint = [], $insertForce = false)
    {
        try {
            if (!$insertForce && (!empty($constraint) || $this->getId())) {
                if (empty($constraint)) {
                    $constraint = [$this->primaryKey => $this->getId()];
                }
                $this->beforeSave();
                $this->update($this->prepareColumns(), $constraint);
                $this->afterSave();
                $id = array_values($constraint)[0];
                $key = array_keys($constraint)[0];
                $this->flushRow($id, null, $this->getCacheKey(), $key === $this->primaryKey ? null : $key);
                $this->flushList($this->getCacheKey());
            } else if ($this->isNew) {
                $this->beforeSave();
                $this->insert($this->prepareColumns());
                $this->setId($this->getTableGateway($this->tableName)->getLastInsertValue());
                $this->afterSave();
                $this->flushList($this->getCacheKey());
            }
        } catch (InvalidQueryException $e) {
            $this->getContainer()->get('log')->logException($e);
            if ($this->transaction) {
                $this->rollback();
            }
            throw $e;
        } catch (Exception $e) {
            $this->getContainer()->get('log')->logException($e);
            throw $e;
        }
        return $this;
    }

    public function remove()
    {
        if ($this->getId()) {
            try {
                $this->beforeRemove();
                $this->delete([$this->primaryKey => $this->getId()]);
                $this->flushRow($this->getId(), null, $this->getCacheKey());
                $this->flushList($this->getCacheKey());
                $this->storage = [];
                $this->isLoaded = false;
                $this->isNew = true;
                $this->updatedColumns = [];
                $this->afterRemove();
            } catch (InvalidQueryException $e) {
                $this->getContainer()->get('log')->logException($e);
                if ($this->transaction) {
                    $this->rollback();
                }
                throw $e;
            } catch (Exception $e) {
                $this->getContainer()->get('log')->logException($e);
                throw $e;
            }
        }
    }

    /**
     * Get table columns
     * 
     * @return array
     */
    public function getColumns()
    {
        if (empty($this->columns)) {
            $cache = $this->getContainer()->get('cache');
            $columns = $cache->fetch($this->tableName, 'TABLE_DESCRIPTION_');
            if (!$columns) {
                $columns = $this->getTableGateway($this->tableName)->getAdapter()->query('DESCRIBE ' . $this->getTableGateway($this->tableName)->getTable(), 'execute');
                $cache->save($this->tableName, $columns, 'TABLE_DESCRIPTION_');
            }
            foreach ($columns as $column) {
                $this->columns[] = $column['Field'];
            }
        }
        return $this->columns;
    }

    /**
     * Get inserting/updating values
     * 
     * @return array
     */
    protected function prepareColumns()
    {
        $columns = $this->getColumns();
        $pairs = [];
        foreach ($this->storage as $key => $value) {
            if (in_array($key, $columns) && ($this->isNew || in_array($key, $this->updatedColumns))) {
                $pairs[$key] = $value;
            }
        }
        return $pairs;
    }

    /**
     * Get event dispatcher
     * 
     * @return type
     */
    protected function getEventDispatcher()
    {
        if (is_null($this->eventDispatcher)) {
            $this->eventDispatcher = $this->getContainer()->get('eventDispatcher');
        }
        return $this->eventDispatcher;
    }

    /**
     * Event before save
     */
    protected function beforeSave()
    {
        $this->getEventDispatcher()->trigger(get_class($this) . '.model.save.before', ['model' => $this]);
    }

    /**
     * Event after save
     */
    protected function afterSave()
    {
        $this->getEventDispatcher()->trigger(get_class($this) . '.model.save.after', ['model' => $this]);
    }

    /**
     * Event before load
     * 
     * @param \Zend\Db\Sql\Select $select
     */
    protected function beforeLoad($select)
    {
        $this->getEventDispatcher()->trigger(get_class($this) . '.model.load.before', ['model' => $this]);
    }

    /**
     * Event after load
     * 
     * @param array $result
     */
    protected function afterLoad($result = [])
    {
        $this->isNew = false;
        $this->isLoaded = true;
        $this->updatedColumns = [];
        if (isset($result[0])) {
            $this->storage = array_merge($this->storage, $result[0]);
        } else {
            $this->storage = array_merge($this->storage, $result);
        }
        $this->getEventDispatcher()->trigger(get_class($this) . '.model.load.after', ['model' => $this]);
    }

    /**
     * Event before remove
     */
    protected function beforeRemove()
    {
        $this->getEventDispatcher()->trigger(get_class($this) . '.model.remove.before', ['model' => $this]);
    }

    /**
     * Event after remove
     */
    protected function afterRemove()
    {
        $this->getEventDispatcher()->trigger(get_class($this) . '.model.remove.after', ['model' => $this]);
    }

    /**
     * Is the model loaded
     * 
     * @return bool
     */
    public function isLoaded()
    {
        return $this->isLoaded;
    }

}
