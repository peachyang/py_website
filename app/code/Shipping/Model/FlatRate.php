<?php

namespace Seahinet\Shipping\Model;

class FlatRate extends AbstractMethod
{
    
    const METHOD_CODE = 'flat_rate';

    public function getShippingRate()
    {
        
    }

    public function isValid()
    {
        return $this->getContainer()->get('config')['shipping/' . self::METHOD_CODE . '/enable'];
    }

}
