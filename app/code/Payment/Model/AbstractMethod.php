<?php

namespace Seahinet\Payment\Model;

abstract class AbstractMethod
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\Url;

    /**
     * @param array $data
     * @return bool|string
     */
    public function available($data = [])
    {
        $config = $this->getContainer()->get('config');
        return $config['payment/' . static::METHOD_CODE . '/enable'] &&
                ($config['payment/' . static::METHOD_CODE . '/max_total'] === '' ||
                $config['payment/' . static::METHOD_CODE . '/max_total'] >= $data['total']) &&
                $config['payment/' . static::METHOD_CODE . '/min_total'] <= $data['total'];
    }

    /**
     * @param array $orders
     * @return string
     */
    public function preparePayment($orders)
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

    public function saveData($cart, $data)
    {
        return $this;
    }

    public function syncNotice($data)
    {
        return '';
    }

    public function asyncNotice($data)
    {
        return '';
    }

}
