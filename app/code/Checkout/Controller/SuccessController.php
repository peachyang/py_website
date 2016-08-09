<?php

namespace Seahinet\Checkout\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Sales\Model\Cart;

class SuccessController extends ActionController
{

    public function indexAction()
    {
//        if (count(Cart::instance()->getItems())) {
            return $this->getLayout('checkout_order_success');
//        }
        return $this->redirectReferer('checkout/cart/');
    }

}
