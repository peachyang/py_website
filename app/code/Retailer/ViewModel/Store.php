<?php

namespace Seahinet\Retailer\ViewModel;

use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\Retailer\Model\Retailer;

class Store extends Template
{

    protected $retailer = null;

    public function getRetailer()
    {
        if (is_null($this->retailer)) {
            $this->retailer = new Retailer;
            $segment = new Segment('customer');
            $this->retailer->load($segment->get('customer')->getId(), 'customer_id');
        }
        return $this->retailer;
    }

    public function getStore()
    {
        return $this->getRetailer()->getStore();
    }

}
