<?php

namespace Seahinet\Admin\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class User extends AbstractCollection
{
    
    protected function _construct()
    {
        $this->init('admin_user');
    }
}
