<?php

namespace Seahinet\Catalog\Model;

use Seahinet\Lib\Model\AbstractModel;

class Logview extends AbstractModel
{

    protected function construct()
    {
        $this->init('log_view', 'customer_id,product_id', ['customer_id', 'product_id', 'created_at', 'updated_at']);
    }

}