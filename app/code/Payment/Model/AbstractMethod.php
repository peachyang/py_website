<?php

namespace Seahinet\Payment\Model;

use Seahinet\Sales\Model\Order\Phase;

abstract class AbstractMethod
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\Url;

    protected $label;

    /**
     * @return bool
     */
    abstract public function available();

    /**
     * @param array $orders
     * @return string
     */
    public function preparePayment()
    {
        return $this->getBaseUrl('checkout/success/');
    }

    /**
     * @return int
     */
    public function getStatusBeforePayment()
    {
        $phase = new Phase;
        $phase->load('pending', 'code');
        return $phase->getDefaultStatus();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    public function saveData(array $data)
    {
        return $this;
    }

}
