<?php

namespace Seahinet\Payment\Model;

class BankTransfer extends AbstractMethod
{

    const METHOD_CODE = 'bank_transfer';

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
