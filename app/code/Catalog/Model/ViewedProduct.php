<?php

namespace Seahinet\Catalog\Model;

use Seahinet\Lib\Model\AbstractModel;

class ViewedProduct extends AbstractModel
{

    use \Seahinet\Lib\Traits\Url;

    protected function construct()
    {
        $this->init('log_viewed_product', 'customer_id', ['customer_id', 'product_id', 'created_at', 'updated_at']);
    }

    public function offsetGet($key)
    {
        $result = parent::offsetGet($key);
        if (!$result && $key === 'product' && !empty($this->storage['product_id'])) {
            $result = new Product;
            $result->load($this->storage['product_id']);
        }
        return $result;
    }

}
