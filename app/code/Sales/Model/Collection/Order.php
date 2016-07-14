<?php

namespace Seahinet\Sales\Model;

use Seahinet\Lib\Model\AbstractCollection;

class Order extends AbstractCollection
{

    protected function construct()
    {
        $this->init('sales_order');
    }

}
