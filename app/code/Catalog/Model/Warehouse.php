<?php

namespace Seahinet\Catalog\Model;

use Seahinet\Lib\Model\AbstractModel;

class Warehouse extends AbstractModel
{

    protected function construct()
    {
        $this->init('warehouse', 'id', ['name', 'country', 'region', 'city', 'address', 'contact_info', 'longitude', 'latitude', 'open_at', 'close_at', 'status']);
    }

}
