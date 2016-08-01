<?php

namespace Seahinet\Payment\Model;

class BankTransfer extends AbstractMethod
{

    const METHOD_CODE = 'bank_transfer';

    public function isValid()
    {
        return $this->getContainer()->get('config')['payment/' . self::METHOD_CODE . '/enable'];
    }

}
