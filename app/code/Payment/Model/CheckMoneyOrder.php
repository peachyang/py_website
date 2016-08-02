<?php

namespace Seahinet\Payment\Model;

class CheckMoneyOrder extends AbstractMethod
{

    const METHOD_CODE = 'check_money_order';

    public function available()
    {
        return $this->getContainer()->get('config')['payment/' . self::METHOD_CODE . '/enable'];
    }

    public function getDescription()
    {
        $description = $this->getContainer()->get('config')['payment/' . self::METHOD_CODE . '/description'];
        return $description ? nl2br($description) : '';
    }

}
