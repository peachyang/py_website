<?php

namespace Seahinet\Retailer\Model;

use Seahinet\Lib\Model\AbstractModel;

class StorePicinfo extends AbstractModel
{

    protected function construct()
    {
        $this->init('store_decoration_picinfo', 'id', ['id', 'store_id', 'resource_id', 'pic_title', 'url', 'resource_category_code', 'sort_order', 'template_id', 'part_id']);
    }

}
