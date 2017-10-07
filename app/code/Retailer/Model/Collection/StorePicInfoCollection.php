<?php

namespace Seahinet\Retailer\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class StorePicInfoCollection extends AbstractCollection
{

    protected function construct()
    {
        $this->init('store_decoration_picinfo', 'id', ['id', 'store_id', 'resource_id', 'pic_title', 'url', 'resource_category_code', 'order_id']);
    }

}
