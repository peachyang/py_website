<?php

namespace Seahinet\Customer\Model;

use Seahinet\Lib\Model\AbstractEAVModel;

class Customer extends AbstractEAVModel
{

    protected function construct()
    {
        $this->init('customer');
    }

}
