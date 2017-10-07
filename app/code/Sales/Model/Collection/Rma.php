<?php

namespace Seahinet\Sales\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Rma extends AbstractCollection
{

    protected function construct()
    {
        $this->init('sales_rma');
    }

}
