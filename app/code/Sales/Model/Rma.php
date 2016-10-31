<?php

namespace Seahinet\Sales\Model;

use Seahinet\Lib\Model\AbstractModel;

class Rma extends AbstractModel
{

    protected function construct()
    {
        $this->init('sales_rma', 'id', ['id', 'order_id', 'customer_id', 'amount', 'carrier', 'track_number', 'comment', 'status', 'created_at', 'updated_at']);
    }

}
