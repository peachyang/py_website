<?php

namespace Seahinet\Retailer\ViewModel\Sales\Edit;

use Seahinet\Admin\ViewModel\Sales\Edit\Address as PAddress;

class Address extends PAddress
{

    public function getSaveUrl()
    {
        return $this->getBaseUrl('retailer/sales_order/saveAddress/');
    }

}
