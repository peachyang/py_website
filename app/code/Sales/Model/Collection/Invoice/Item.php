<?php

namespace Seahinet\Sales\Model\Collection\Invoice;

use Seahinet\Lib\Model\AbstractCollection;

class Item extends AbstractCollection
{

    protected function construct()
    {
        $this->init('sales_order_invoice_item');
    }

}
