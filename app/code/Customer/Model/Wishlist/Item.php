<?php

namespace Seahinet\Customer\Model\Wishlist;

use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\Model\AbstractModel;

class Item extends AbstractModel
{

    protected function construct()
    {
        $this->init('wishlist_item', 'id', [
            'id', 'wishlist_id', 'product_id', 'product_name', 'sku', 'warehouse_id',
            'store_id', 'qty', 'options', 'description', 'price'
        ]);
    }

    public function getProduct()
    {
        $product = new Product;
        $product->load($this->storage['product_id']);
        return $product;
    }

    public function afterRemove()
    {
        $this->flushList('wishlist');
        parent::afterRemove();
    }

}
