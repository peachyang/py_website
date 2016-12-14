<?php

namespace Seahinet\Retailer\ViewModel;

use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\Retailer\Model\Retailer;

abstract class AbstractViewModel extends Template
{

    private static $retailer = null;
    private static $store = null;

    public function getRetailer()
    {
        if (is_null(self::$retailer)) {
            self::$retailer = new Retailer;
            $segment = new Segment('customer');
            self::$retailer->load($segment->get('customer')->getId(), 'customer_id');
        }
        return self::$retailer;
    }

    public function getStore()
    {
        if (is_null(self::$store)) {
            self::$store = $this->getRetailer()->getStore();
        }
        return self::$store;
    }

}
