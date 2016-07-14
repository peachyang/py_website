<?php

namespace Seahinet\Sales\Model;

use Seahinet\Lib\Model\AbstractModel;

class CreditMemo extends AbstractModel
{

    protected function construct()
    {
        $this->init('sales_order_creditmemo', 'id', [
            'id', 'order_id', 'increment_id', 'store_id', 'warehouse_id',
            'base_currency', 'currency', 'base_shipping', 'shipping',
            'base_discount', 'discount', 'base_tax', 'tax',
            'base_total', 'total', 'comment', 'status'
        ]);
    }

}
