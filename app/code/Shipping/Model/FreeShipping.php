<?php

namespace Seahinet\Shipping\Model;

class FreeShipping implements MethodInterface
{

    public function getShippingRate()
    {
        return 0;
    }

}
