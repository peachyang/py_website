<?php

namespace Seahinet\Sales\Model\Rma;

use Seahinet\Lib\Model\AbstractModel;

class Item extends AbstractModel
{

    protected $product = null;

    protected function construct()
    {
        $this->init('sales_rma_item', 'id', [
            'id', 'item_id', 'rma_id','qty'
        ]);
    }

}
