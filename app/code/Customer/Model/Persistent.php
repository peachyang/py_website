<?php

namespace Seahinet\Customer\Model;

use Seahinet\Lib\Model\AbstractModel;

class Persistent extends AbstractModel
{

    protected function construct()
    {
        $this->init('persistent', 'customer_id', ['key', 'customer_id']);
    }

}
