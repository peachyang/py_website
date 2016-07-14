<?php

namespace Seahinet\Sales\Model\CreditMemo;

use Seahinet\Lib\Model\AbstractModel;

class Item extends AbstractModel
{

    protected function construct()
    {
        $this->init('sales_order_creditmemo_item', 'id', [
            'id', 'item_id', 'creditmemo_id', 'product_id', 'product_name',
            'options', 'qty', 'sku', 'base_price', 'price', 'base_discount',
            'discount', 'base_tax', 'tax', 'base_total', 'total'
        ]);
    }

}
