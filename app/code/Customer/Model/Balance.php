<?php

namespace Seahinet\Customer\Model;

use Seahinet\Lib\Model\AbstractModel;

class Balance extends AbstractModel
{

    protected function construct()
    {
        $this->init('customer_balance', 'id', ['id','customer_id','order_id', 'amount', 'comment', 'status']);
    }

}
