<?php

namespace Seahinet\Payment\Model;

class CheckMoneyOrder extends AbstractMethod
{

    const METHOD_CODE = 'check_money_order';

    public function isValid()
    {
        return $this->getContainer()->get('config')['payment/' . self::METHOD_CODE . '/enable'];
    }

}
