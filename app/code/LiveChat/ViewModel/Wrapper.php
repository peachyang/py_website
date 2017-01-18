<?php

namespace Seahinet\LiveChat\ViewModel;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\LiveChat\Model\Collection\Session;
use Seahinet\Retailer\Model\Retailer;

class Wrapper extends Template
{

    protected $customerId;

    public function getCustomerId()
    {
        $this->customerId = $this->getSegment('customer')->get('customer')->getId();
        return $this->customerId;
    }

    public function getSessions()
    {
        $id = $this->getCustomerId();
        $collection = new Session;
        $collection->where(['customer_id_1' => $id, 'customer_id_2' => $id], 'OR');
        return $collection->load(true, true);
    }

    public function getName($id)
    {
        $retailer = new Retailer;
        $retailer->load($id, 'customer_id');
        if ($retailer->getId()) {
            return $retailer->getStore()['name'];
        } else {
            $customer = new Customer;
            $customer->load($id);
            return $customer['username'];
        }
    }

}
