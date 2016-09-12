<?php

namespace Seahinet\Retailer\Model;

use Seahinet\Lib\Model\AbstractModel;

class Application extends AbstractModel
{

    protected function construct()
    {
        $this->init('retailer_application', 'id', ['id', 'customer_id', 'lisence_1', 'lisence_2', 'phone', 'brand_type', 'product_type', 'status']);
    }

}
