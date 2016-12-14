<?php

namespace Seahinet\Sales\Model\Collection\Rma;

use Seahinet\Lib\Model\AbstractCollection;

class Item extends AbstractCollection
{

    protected function construct()
    {
        $this->init('sales_rma_item');
    }

}
