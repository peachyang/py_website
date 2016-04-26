<?php

namespace Seahinet\Admin\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Operation extends AbstractCollection
{

    protected function _construct()
    {
        $this->init('admin_operation');
    }

    public function orderByRole()
    {
        $this->select->join('admin_permission', 'admin_permission.operation_id = admin_operation.id', [], 'left');
        $this->select->join('admin_role', 'admin_permission.role_id = admin_role.id', ['role' => 'name', 'role_id' => 'id'], 'left');
        $this->select->order('admin_operation.name');
        return $this;
    }

}
