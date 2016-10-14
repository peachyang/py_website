<?php
namespace Seahinet\Sales\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;

class CouponController
{
    public function indexAction()
    {
        $root = $this->getLayout('customer_dashboard_coupon');
        return $root;
    }
}
