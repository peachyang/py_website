<?php

namespace Seahinet\Lib\Model;

use Exception;
use Zend\Stdlib\ArrayObject;

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

    abstract protected function _construct();

    protected function init($table, $primaryKey = 'id', $columns = [])
    {
        $this->tableName = $table;
        $this->getTableGateway($table);
        $this->cacheKey = $table . '\\';
        $this->columns = $columns;
        $this->$primaryKey = $primaryKey;
    }

    protected function withLanguage($table, $column)
    {
        $this->languageInfo = [$table, $column];
    }

    public function getId()
    {
        return isset($this->storage[$this->primaryKey]) ? $this->storage[$this->primaryKey] : null;
    }

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

    public function load($id, $key = null)
    {
        if (!$this->isLoaded) {
            try {
                if (is_null($key)) {
                    $key = $this->primaryKey;
                }
                $cache = $this->getContainer()->get('cache');
                $result = $cache->fetch($this->cacheKey . $key . '\\' . $id);
                if (!$result) {
                    $this->beforeLoad();
                    $select = $this->tableGateway->getSql()->select();
                    $select->where([$this->tableName . '.' . $key => $id]);
                    if (!is_null($this->languageInfo)) {
                        $select->join($this->languageInfo[0], $this->tableName . '.' . $this->primaryKey . '=' . $this->languageInfo[0] . '.' . $this->languageInfo[1], '*', 'left');
                        $select->join('core_language', 'core_language.id=' . $this->languageInfo[0] . '.' . $this->languageInfo[1], '*', 'left');
                    }
                    $result = $this->tableGateway->selectWith($select)->toArray();
                    $this->storage = array_merge($this->storage, $result[0]);
                    if (!is_null($this->languageInfo)) {
                        $this->storage['language'] = [];
                        foreach ($result as $record){
                            $this->storage['language'][] = $record;
                        }
                    }
                    $this->afterLoad();
                    $cache->save($this->cacheKey . $key . '\\' . $id, $this->storage);
                }
                $this->isNew = false;
                $this->isLoaded = true;
                $this->updatedColumns = [];
            } catch (Exception $e) {
                
            }
        }
        return $this;
    }

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
                $cache->delete($this->cacheKey . implode('\\', $constraint));
            } else {
                
            }
        } catch (Exception $e) {
            
        }
    }

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
        $this->getEventDispatcher()->trigger(get_class($this) . '.model.save.after', ['model' => $this]);
    }

    protected function beforeLoad()
    {
        $this->getEventDispatcher()->trigger(get_class($this) . '.model.load.before', ['model' => $this]);
    }

    protected function afterLoad()
    {
        $this->getEventDispatcher()->trigger(get_class($this) . '.model.load.after', ['model' => $this]);
    }

}
