<?php

namespace Seahinet\Shipping\Model;

abstract class AbstractMethod
{
    
    use \Seahinet\Lib\Traits\Container;

    abstract public function isValid();
    
    abstract public function getShippingRate();

}
