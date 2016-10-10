<?php

namespace Seahinet\Retailer\Model;

use Seahinet\Lib\Model\AbstractModel;

class Category extends AbstractModel
{

    protected function construct()
    {
        $this->init('retailer_category', 'id', ['id', 'parent_id', 'default_name', 'store_id', 'uri_key']);
    }

}
