<?php

namespace Seahinet\Payment\Model;

class Free extends AbstractMethod
{

    const METHOD_CODE = 'payment_free';

    public function available()
    {
        return true;
    }

}