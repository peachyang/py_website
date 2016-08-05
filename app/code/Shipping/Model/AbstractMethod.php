<?php

namespace Seahinet\Shipping\Model;

abstract class AbstractMethod
{

    use \Seahinet\Lib\Traits\Container;

    abstract public function available();

    abstract public function getShippingRate($items);

    public function getLabel()
    {
        return $this->getContainer()->get('config')['shipping/' . static::METHOD_CODE . '/label'];
    }

}
