<?php

namespace Seahinet\Promotion\Model;

use Seahinet\Lib\Model\AbstractModel;

class Promotion extends AbstractModel
{

    protected function construct()
    {
        $this->init('promotion', 'id', ['id', 'name', 'description', 'status', 'from_date', 'to_date', 'stop_processing', 'sort_order']);
    }

}
