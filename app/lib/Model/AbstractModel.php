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

    public function __construct($input = array(), $flags = self::ARRAY_AS_PROPS, $iteratorClass = 'ArrayIterator')
    {
        parent::__construct($input, $flags, $iteratorClass);
        $this->_construct();
    }

    abstract protected function _construct();

    protected function init($table, $primaryKey = 'id', $columns = [])
    {
        $this->getTableGateway($table);
        $this->cacheKey = $table . '\\';
        $this->columns = $columns;
        $this->$primaryKey = $primaryKey;
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
                    $result = $this->select([$key => $id])->toArray();
                    $cache->save($this->cacheKey . $key . '\\' . $id, $result);
                }
                $this->storage = array_merge($this->storage, $result);
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
                $this->insert($columns);
            } else if (!empty($constraint) && $this->getId()) {
                if (empty($constraint)) {
                    $constraint = [$this->primaryKey => $this->getId()];
                }
                $this->update($columns, $constraint);
                $cache = $this->getContainer()->get('cache');
                $cache->delete($constraint);
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

}
