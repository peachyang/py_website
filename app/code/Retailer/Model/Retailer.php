<?php

namespace Seahinet\Retailer\Model;

use Seahinet\Lib\Model\AbstractModel;

class Retailer extends AbstractModel
{

    protected function construct()
    {
        $this->init('retailer', 'id', ['id', 'customer_id', 'store_id', 'name', 'address', 'account', 'photo', 'credentials', 'status']);
    }

}
