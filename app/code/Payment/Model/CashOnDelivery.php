<?php

namespace Seahinet\Payment\Model;

class CashOnDelivery extends AbstractMethod
{

    const METHOD_CODE = 'cash_on_delivery';

    public function isValid()
    {
        return $this->getContainer()->get('config')['payment/' . self::METHOD_CODE . '/enable'];
    }

}
