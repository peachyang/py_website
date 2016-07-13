<?php

namespace Seahinet\Sales\Model\Collection\CreditMemo;

use Seahinet\Lib\Model\AbstractCollection;

class Item extends AbstractCollection
{

    protected function construct()
    {
        $this->init('sales_order_creditmemo_item');
    }

}
