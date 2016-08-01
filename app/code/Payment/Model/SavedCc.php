<?php

namespace Seahinet\Payment\Model;

class SavedCc extends AbstractMethod
{

    const METHOD_CODE = 'saved_cc';

    public function isValid()
    {
        return $this->getContainer()->get('config')['payment/' . self::METHOD_CODE . '/enable'];
    }

}
