<?php

namespace Seahinet\Lib\Model;

use Exception;
use Seahinet\Lib\Bootstrap;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Exception\InvalidQueryException;
use Zend\Stdlib\ArrayObject;

/**
 * Data operator for single model
 */
abstract class AbstractModel extends ArrayObject
{

    use \Seahinet\Lib\Traits\DB,
        \Seahinet\Lib\Traits\DataCache;

    protected $columns = [];
    protected $updatedColumns = [];
    protected $primaryKey = 'id';
    protected $isNew = true;
    protected $isLoaded = false;
    protected $cacheKey = '';
    protected $eventDispatcher = null;
    protected $languageInfo = null;
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
        $this->cacheKey = $table . '\\';
        $this->columns = $columns;
        $this->primaryKey = $primaryKey;
    }

    /**
     * Join language table
     * 
     * @param string $table
     * @param string $column
     */
    protected function withLanguage($table, $column)
    {
        $this->languageInfo = [$table, $column];
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
                    $this->beforeLoad();
                    $select = $this->tableGateway->getSql()->select();
                    $select->where([$this->tableName . '.' . $key => $id]);
                    if (!is_null($this->languageInfo)) {
                        $select->join($this->languageInfo[0], $this->tableName . '.' . $this->primaryKey . '=' . $this->languageInfo[0] . '.' . $this->languageInfo[1], [], 'left');
                        $select->join('core_language', 'core_language.id=' . $this->languageInfo[0] . '.language_id', ['language_id' => 'id', 'language' => 'code'], 'left');
                        if ($key !== $this->primaryKey) {
                            $select->where(['core_language.id' => Bootstrap::getLanguage()->getId()]);
                        }
                    }
                    $result = $this->tableGateway->selectWith($select)->toArray();
                    if (count($result)) {
                        $this->storage = array_merge($this->storage, $result[0]);
                        if (!is_null($this->languageInfo)) {
                            $this->storage['language'] = [];
                            foreach ($result as $record) {
                                $this->storage['language'][$record['language_id']] = $record['language'];
                            }
                        }
                        $this->afterLoad();
                        $this->flushRow($this->storage[$this->primaryKey], $this->storage, $this->getCacheKey());
                        if ($key !== $this->primaryKey) {
                            $this->addCacheAlias($key, $this->storage[$this->primaryKey], $this->getCacheKey());
                        }
                    }
                } else {
                    $this->storage = array_merge($this->storage, $result);
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

    /**
     * Insert/Update data
     * 
     * @param array $constraint     Update query constraint
     * @return AbstractModel
     */
    public function save($constraint = [], $insertForce = false)
    {
        $columns = $this->prepareColumns();
        try {
            if (!$insertForce && (!empty($constraint) || $this->getId())) {
                if (empty($constraint)) {
                    $constraint = [$this->primaryKey => $this->getId()];
                }
                $this->beforeSave();
                $this->update($columns, $constraint);
                $this->afterSave();
                $id = array_values($constraint)[0];
                $key = array_keys($constraint)[0];
                $this->flushRow($id, null, $this->getCacheKey(), $key === $this->primaryKey ? null : $key);
                $this->load($id, $key);
                $this->flushList($this->getCacheKey());
            } else if ($this->isNew) {
                $this->beforeSave();
                $this->insert($columns);
                $this->load($this->tableGateway->getLastInsertValue());
                $this->afterSave();
                $this->flushList($this->getCacheKey());
            }
        } catch (InvalidQueryException $e) {
            $this->getContainer()->get('log')->logException($e);
            throw $e;
        } catch (Exception $e) {
            $this->getContainer()->get('log')->logException($e);
            throw $e;
        }
        return $this;
    }

    public function remove()
    {
        if ($this->isLoaded) {
            try {
                $this->beforeRemove();
                $key = $this->cacheKey . $this->primaryKey . '\\' . $this->getId();
                $this->delete([$this->primaryKey => $this->getId()]);
                $cache = $this->getContainer()->get('cache');
                $cache->delete($key, 'MODEL_DATA_');
                $this->storage = [];
                $this->isLoaded = false;
                $this->isNew = true;
                $this->updatedColumns = [];
                $this->afterRemove();
            } catch (InvalidQueryException $e) {
                $this->getContainer()->get('log')->logException($e);
            } catch (Exception $e) {
                $this->getContainer()->get('log')->logException($e);
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
            $columns = $cache->fetch($this->tableGateway->getTable(), 'TABLE_DESCRIPTION_');
            if (!$columns) {
                $columns = $this->tableGateway->getAdapter()->query('DESCRIBE ' . $this->tableGateway->getTable(), 'execute');
                $cache->save($this->tableGateway->getTable(), $columns, 'TABLE_DESCRIPTION_');
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
            if (in_array($key, $columns) && $this->isNew || in_array($key, $this->updatedColumns)) {
                $pairs[$key] = $value;
            }
        }
        return $pairs;
    }

    protected function getEventDispatcher()
    {
        if (is_null($this->eventDispatcher)) {
            $this->eventDispatcher = $this->getContainer()->get('eventDispatcher');
        }
        return $this->eventDispatcher;
    }

    protected function beforeSave()
    {
        $this->getEventDispatcher()->trigger(get_class($this) . '.model.save.before', ['model' => $this]);
    }

    protected function afterSave()
    {
        if (!is_null($this->languageInfo) && $this->getId() && count($this->storage['language_id'])) {
            $tableGateway = new TableGateway($this->languageInfo[0], $this->tableGateway->getAdapter());
            $tableGateway->delete([$this->languageInfo[1] => $this->getId()]);
            foreach ((array) $this->storage['language_id'] as $language) {
                $tableGateway->insert([$this->languageInfo[1] => $this->getId(), 'language_id' => $language]);
            }
        }
        $this->getEventDispatcher()->trigger(get_class($this) . '.model.save.after', ['model' => $this]);
    }

    protected function beforeLoad()
    {
        $this->getEventDispatcher()->trigger(get_class($this) . '.model.load.before', ['model' => $this]);
    }

    protected function afterLoad()
    {
        $this->isNew = false;
        $this->isLoaded = true;
        $this->updatedColumns = [];
        $this->getEventDispatcher()->trigger(get_class($this) . '.model.load.after', ['model' => $this]);
    }

    protected function beforeRemove()
    {
        $this->getEventDispatcher()->trigger(get_class($this) . '.model.remove.before', ['model' => $this]);
    }

    protected function afterRemove()
    {
        $this->getEventDispatcher()->trigger(get_class($this) . '.model.remove.after', ['model' => $this]);
    }

    public function serialize()
    {
        $data = get_object_vars($this);
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                unset($data[$key]);
            }
        }
        return serialize($data);
    }

    public function unserialize($data)
    {
        $data = unserialize($data);
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
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
