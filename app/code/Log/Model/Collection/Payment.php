<?php

namespace Seahinet\Log\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Payment extends AbstractCollection
{

    protected function construct()
    {
        $this->init('log_payment');
    }

}
