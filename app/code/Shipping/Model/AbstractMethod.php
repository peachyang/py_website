<?php

namespace Seahinet\Shipping\Model;

abstract class AbstractMethod
{

    use \Seahinet\Lib\Traits\Container;

    protected $label;

    abstract public function available();

    abstract public function getShippingRate($items);

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

}
