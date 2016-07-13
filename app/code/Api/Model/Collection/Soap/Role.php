<?php

namespace Seahinet\Api\Model\Collection\Soap;
use Seahinet\Lib\Model\AbstractCollection;

class Role extends AbstractCollection
{
    public $i = 0;
    protected function construct() {
        $this->init('api_soap_role');
    }
    
   public function addOperation()
   {
        $this->select->join('api_soap_permission', 'api_soap_permission.role_id = api_soap_role.id', [], 'left');
        $this->select->join('api_soap_operation', 'api_soap_permission.operation_id = api_soap_operation.id', ['operation' => 'name'], 'left');
        $this->select->where(['permission' => 1]);
        return $this;
    }    
    
    public function addChildren()
    {
        $this->select->join('api_soap_role_recursive', 'api_soap_role_recursive.role_id = api_soap_role.id', ['child_id'], 'left');
        return $this;
    }
    
    protected function afterLoad($result) {
        parent::afterLoad($result);
        if(isset($this->storage[0]['operation']) || isset($this->storage[0]['child_id'])) {
            $storage = [];
            
            foreach ($this->storage as $item)
            {
                if(isset($storage[$item['id']])) {
                    if(isset($item['operation'])) {
                        $storage[$item['id']]['operation'][] = $item['operation'];
                    }
                    
                }
            }
        }
    }
}