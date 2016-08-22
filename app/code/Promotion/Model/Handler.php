<?php

namespace Seahinet\Promotion\Model;

use Seahinet\Lib\Model\AbstractModel;

class Handler extends AbstractModel
{

    protected function construct()
    {
        $this->init('promotion_handler', 'id', ['id', 'promotion_id', 'qty', 'price', 'is_fixed', 'per_item', 'free_shipping', 'apply_to_subtotal']);
    }

}
