<?php

namespace Seahinet\Admin\ViewModel\Sales\Edit;

use Seahinet\Admin\ViewModel\Customer\Edit\Address as PAddress;

class Address extends PAddress
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('sales_order/saveAddress/');
    }

    public function getElements()
    {
        $result = parent::getElements();
        $result['order_id'] = [
            'type' => 'hidden',
            'value' => $this->getQuery('id')
        ];
        $result['is_billing'] = [
            'type' => 'hidden'
        ];
        unset($result['is_default']);
        return $result;
    }

}
