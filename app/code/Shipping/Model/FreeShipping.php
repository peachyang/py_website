<?php

namespace Seahinet\Shipping\Model;

class FreeShipping extends AbstractMethod
{
    
    const METHOD_CODE = 'free_shipping';

    public function getShippingRate()
    {
        
    }

    public function isValid()
    {
        return $this->getContainer()->get('config')['shipping/' . self::METHOD_CODE . '/enable'];
    }

}
