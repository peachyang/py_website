<?php

namespace Seahinet\Checkout\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;

class FailedController extends ActionController
{

    public function indexAction()
    {
        $segment = new Segment('checkout');
        if ($segment->get('hasNewOrder')) {
            return $this->getLayout('checkout_order_failed');
        }
        return $this->redirectReferer('checkout/cart/');
    }

}
