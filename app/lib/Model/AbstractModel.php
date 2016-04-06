<?php

namespace Seahinet\Lib\Model;

use Exception;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Exception\InvalidQueryException;
use Zend\Stdlib\ArrayObject;

/**
 * Data operator for single model
 */
abstract class AbstractModel extends ArrayObject
{

    use \Seahinet\Lib\Traits\DB;

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
                if (is_null($key)) {
                    $key = $this->primaryKey;
                }
                $cache = $this->getContainer()->get('cache');
                $result = $cache->fetch('MODEL_DATA_' . $this->cacheKey . $key . '\\' . $id);
                if (!$result) {
                    $this->beforeLoad();
                    $select = $this->tableGateway->getSql()->select();
                    $select->where([$this->tableName . '.' . $key => $id]);
                    if (!is_null($this->languageInfo)) {
                        $select->join($this->languageInfo[0], $this->tableName . '.' . $this->primaryKey . '=' . $this->languageInfo[0] . '.' . $this->languageInfo[1], [], 'left');
                        $select->join('core_language', 'core_language.id=' . $this->languageInfo[0] . '.language_id', ['language_id' => 'id', 'language' => 'code'], 'left');
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
                        $cache->save('MODEL_DATA_' . $this->cacheKey . $key . '\\' . $id, $this->storage, 86400);
                    }
                } else {
                    $this->storage = array_merge($this->storage, $result);
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

    /**
     * Insert/Update data
     * 
     * @param array $constraint     Update query constraint
     * @return AbstractModel
     */
    public function save($constraint = [])
    {
        $columns = $this->prepareColumns();
        try {
            if ($this->isNew) {
                $this->beforeSave();
                $this->insert($columns);
                $this->setId($this->tableGateway->getLastInsertValue());
                $this->afterSave();
            } else if (!empty($constraint) && $this->getId()) {
                if (empty($constraint)) {
                    $constraint = [$this->primaryKey => $this->getId()];
                }
                $this->beforeSave();
                $this->update($columns, $constraint);
                $this->afterSave();
                $cache = $this->getContainer()->get('cache');
                $cache->delete('MODEL_DATA_' . $this->cacheKey . implode('\\', $constraint));
            }
        } catch (InvalidQueryException $e) {
            $this->getContainer()->get('log')->logException($e);
        } catch (Exception $e) {
            $this->getContainer()->get('log')->logException($e);
        }
        return $this;
    }

    public function remove()
    {
        if ($this->isLoaded) {
            try {
                $this->beforeRemove();
                $key = 'MODEL_DATA_' . $this->cacheKey . $this->primaryKey . '\\' . $this->getId();
                $this->delete([$this->primaryKey => $this->getId()]);
                $cache = $this->getContainer()->get('cache');
                $cache->delete($key);
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
     * Get inserting/updating values
     * 
     * @return array
     */
    protected function prepareColumns()
    {
        if (empty($this->columns)) {
            $cache = $this->getContainer()->get('cache');
            $columns = $cache->fetch('TABLE_DESCRIPTION_' . $this->tableGateway->getTable());
            if (!$columns) {
                $columns = $this->tableGateway->getAdapter()->query('DESCRIBE ' . $this->tableGateway->getTable(), 'execute');
                $cache->save('TABLE_DESCRIPTION_' . $this->tableGateway->getTable(), $columns);
            }
            foreach ($columns as $column) {
                $this->columns[] = $column['Field'];
            }
        }
        $columns = [];
        foreach ($this->storage as $key => $value) {
            if (in_array($key, $this->columns) && $this->isNew || in_array($key, $this->updatedColumns)) {
                $columns[$key] = $value;
            }
        }
        return $columns;
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
            $tableGateway->delete([$this->languageInfo[0] => $this->getId()]);
            foreach ($this->storage['language_id'] as $language) {
                $tableGateway->insert([$this->languageInfo[0] => $this->getId(), 'language_id' => $language]);
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

}
