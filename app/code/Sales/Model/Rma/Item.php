<?php

namespace Seahinet\Sales\Model\Rma;

use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\Model\AbstractModel;

class Item extends AbstractModel
{

    protected $product = null;

    protected function construct()
    {
        $this->init('sales_rma_item', 'id', [
            'id', 'item_id', 'rma_id', 'qty'
        ]);
    }

    public function &offsetGet($key)
    {
        $result = parent::offsetGet($key);
        if (!$result) {
            if ($key === 'product' && !empty($this->storage['product_id'])) {
                if (is_null($this->product)) {
                    $this->product = new Product;
                    $this->product->load($this->storage['product_id']);
                }
                $result = $this->product;
            }
        }
        return $result;
    }

}
