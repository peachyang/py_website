<?php

namespace Seahinet\Sales\Model;

use Seahinet\Lib\Model\AbstractModel;

class Invoice extends AbstractModel
{

    protected function construct()
    {
        $this->init('sales_order_invoice', 'id', [
            'id', 'order_id', 'increment_id', 'store_id', 'coupon',
            'base_currency', 'currency', 'base_shipping', 'shipping',
            'base_discount', 'discount', 'base_tax', 'tax',
            'base_total', 'total'
        ]);
    }

}
