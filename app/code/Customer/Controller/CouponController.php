<?php

namespace Seahinet\Customer\Controller;

class CouponController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('customer_coupon');
    }

}
