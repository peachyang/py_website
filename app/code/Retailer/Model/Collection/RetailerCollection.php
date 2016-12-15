<?php

namespace Seahinet\Retailer\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class RetailerCollection extends AbstractCollection
{

    protected function construct()
    {
        $this->init('retailer', 'id', ['id', 'customer_id', 'store_id', 'name', 'address', 'account', 'photo', 'banner', 'credentials', 'status']);
    }

}
