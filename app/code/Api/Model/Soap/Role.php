<?php

namespace Seahinet\Api\Model\Soap;

use Seahinet\Lib\Model\AbstractModel;

class Role extends AbstractModel
{

    protected $role = null;

    protected function construct()
    {
        $this->init('api_soap_role', 'id', ['id', 'name']);
    }

    public function getPermission()
    {
        $result = [];
        if ($this->getId()) {
            $tableGateway = $this->getTableGateway('api_soap_permission');
            $resultSet = $tableGateway->select(['role_id' => $this->getId(), 'permission' => 1])->toArray();
            array_walk($resultSet, function($item) use (&$result) {
                $result[] = $item['resource'];
            });
        }
        return $result;
    }

    protected function beforeSave()
    {
        $this->beginTransaction();
        parent::beforeSave();
    }
    
    protected function afterSave()
    {
        if (!empty($this->storage['resource'])) {
            $tableGateway = $this->getTableGateway('api_soap_permission');
            $tableGateway->delete(['role_id' => $this->getId()]);
            foreach ($this->storage['resource'] as $resource) {
                $tableGateway->insert(['role_id' => $this->getId(), 'resource' => $resource]);
            }
        }
        parent::afterSave();
        $this->commit();
    }

}
