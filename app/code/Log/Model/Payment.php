<?php

namespace Seahinet\Log\Model;

use Seahinet\Lib\Model\AbstractModel;

class Payment extends AbstractModel
{

    protected function construct()
    {
        $this->init('log_payment', 'id', ['id', 'order_id', 'trade_id', 'method', 'params', 'comment', 'is_request']);
    }

}
