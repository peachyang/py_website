<?php

namespace Seahinet\Payment\Model;

abstract class AbstractMethod
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\Url;

    /**
     * @param array $data
     * @return bool
     */
    public function available($data)
    {
        return $this->getContainer()->get('config')['payment/' . static::METHOD_CODE . '/enable'];
    }

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
    public function getNewOrderStatus()
    {
        return $this->getContainer()->get('config')['payment/' . static::METHOD_CODE . '/new_status'];
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $description = $this->getContainer()->get('config')['payment/' . static::METHOD_CODE . '/description'];
        return $description ? nl2br($description) : '';
    }

    public function getLabel()
    {
        return $this->getContainer()->get('config')['payment/' . static::METHOD_CODE . '/label'];
    }

    public function saveData(array $data)
    {
        return $this;
    }

}
