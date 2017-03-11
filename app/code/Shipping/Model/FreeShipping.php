<?php

namespace Seahinet\Shipping\Model;

class FreeShipping extends AbstractMethod
{

    const METHOD_CODE = 'free_shipping';

    public function getShippingRate($storeId)
    {
        return 0;
    }

}
