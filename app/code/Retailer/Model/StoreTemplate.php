<?php

namespace Seahinet\Retailer\Model;

use Seahinet\Lib\Model\AbstractModel;

class StoreTemplate extends AbstractModel
{

    protected function construct()
    {
        $this->init('store_decoration_template','id',['id','template_name','code_model','src_model','status','store_id']);
    }

}