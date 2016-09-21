<?php

namespace Seahinet\Catalog\Model\Collection\Warehouse;

use Seahinet\Lib\Model\AbstractCollection;

class Inventory extends AbstractCollection
{

    protected function construct()
    {
        $this->init('warehouse_inventory');
    }

}
