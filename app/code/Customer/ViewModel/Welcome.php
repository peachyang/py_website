<?php

namespace Seahinet\Customer\ViewModel;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Lib\Session\Segment;

class Welcome extends Template
{

    public function __construct()
    {
        $this->setTemplate('customer/welcome');
    }

    public function getCustomer()
    {
        $segment = new Segment('customer');
        if ($segment->get('isLoggedin')) {
            return $segment->get('customer');
        }
        return false;
    }

}
