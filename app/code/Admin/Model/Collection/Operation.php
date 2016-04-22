<?php

namespace Seahinet\Admin\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Operation extends AbstractCollection
{

    protected function _construct()
    {
        $this->init('admin_operation');
    }

}
