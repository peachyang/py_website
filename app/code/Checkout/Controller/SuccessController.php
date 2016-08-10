<?php

namespace Seahinet\Checkout\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;

class SuccessController extends ActionController
{

    public function indexAction()
    {
        $segment = new Segment('checkout');
        if ($segment->get('hasNewOrder')) {
            $segment->set('hasNewOrder', 0);
            return $this->getLayout('checkout_order_success');
        }
        return $this->redirectReferer('checkout/cart/');
    }

}
