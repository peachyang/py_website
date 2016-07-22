<?php

namespace Seahinet\Checkout\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Sales\Model\Cart;

class OrderController extends ActionController
{

    public function indexAction()
    {
        if (count(Cart::instance()->getItems())) {
            return $this->getLayout('checkout_order');
        }
        return $this->redirectReferer('checkout/cart/');
    }

    public function shippingAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->getLayout('checkout_order_shipping');
        }
        return $this->notFoundAction();
    }

    public function paymentAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->getLayout('checkout_order_payment');
        }
        return $this->notFoundAction();
    }

    public function reviewAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->getLayout('checkout_order_review');
        }
        return $this->notFoundAction();
    }

}
