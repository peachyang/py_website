<?php

namespace Seahinet\Customer\Model;

use Seahinet\Customer\Model\Collection\Wishlist\Item as Collection;
use Seahinet\Customer\Model\Wishlist\Item as Model;
use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Catalog\Model\Product;

class Wishlist extends AbstractModel
{

    protected function construct()
    {
        $this->init('wishlist', 'id', ['id', 'customer_id']);
    }

    public function getItems()
    {
        if ($this->getId()) {
            $items = new Collection;
            $items->where(['wishlist_id' => $this->getId()]);
            return $items;
        }
        return [];
    }

    public function addItem($item)
    {
        $item = new Model($item);
        if (isset($item['product'])) {
            $product = $item['product'];
        } else {
            $product = new Product;
            $product->load($item['product_id'], 'id');
        }
        $item->setData([
            'wishlist_id' => $this->getId(),
            'store_id' => $product['store_id'],
            'product_name' => $product['name'],
            'description' => $product['description'],
        ]);
        $item->save();
        return $this;
    }

}
