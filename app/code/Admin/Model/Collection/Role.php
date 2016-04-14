<?php

namespace Seahinet\Admin\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Role extends AbstractCollection
{

    public $i = 0;

    protected function _construct()
    {
        $this->init('admin_role');
    }

    protected function beforeLoadCache()
    {
        $this->select->join('admin_permission', 'admin_permission.role_id = admin_role.id', [], 'left');
        $this->select->join('admin_operation', 'admin_permission.operation_id = admin_operation.id', ['operation' => 'name'], 'left');
        $this->select->where(['permission' => 1]);
        parent::beforeLoad();
    }

    protected function afterLoad()
    {
        $storage = [];
        foreach ($this->storage as $item) {
            if (isset($storage[$item['id']])) {
                $storage[$item['id']]['operation'][] = $item['operation'];
            } else {
                $storage[$item['id']] = $item;
                $storage[$item['id']]['operation'] = [$item['operation']];
            }
        }
        $this->storage = array_values($storage);
        parent::afterLoad();
    }

}
