<?php

namespace Seahinet\Sales\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class  Inquire extends AbstractCollection
{
    protected function construct()
    {
        $this->init('sales_order');
    }
   
    protected function beforeLoad()
    {
        $this->select->join('address_2_index', 'address_2_index.store_id=sales_order.store_id');
        parent::beforeLoad();
    }
}
