<?php

namespace Seahinet\Lib\Model;

abstract class AbstractEAVModel extends AbstractModel
{

    protected $entityType = null;

    protected function init($entityType, $primaryKey = 'id', $columns = array())
    {
        $this->entityType = $entityType;
        $this->cacheKey = $entityType;
        $this->columns = $columns;
        $this->primaryKey = $primaryKey;
    }

    public function load($id, $key = null)
    {
        
    }

    public function save($constraint = array(), $insertForce = false)
    {
        parent::save($constraint, $insertForce);
    }

}
