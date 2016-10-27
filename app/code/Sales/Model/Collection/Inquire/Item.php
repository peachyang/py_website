<?php

namespace Seahinet\Sales\Model\Collection\Invoice;

use Seahinet\Lib\Model\AbstractCollection;

class Item extends AbstractCollection
{

    protected function contruct()
    {
        $this->init('address_2_index');
    }

}
