<?php

namespace Seahinet\Admin\Model;

use Seahinet\Lib\Model\AbstractModel;

class Operation extends AbstractModel
{

    protected $role = null;

    protected function _construct()
    {
        $this->init('admin_operation', 'id', ['id', 'name', 'description', 'is_system']);
    }

}
