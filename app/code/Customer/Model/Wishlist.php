<?php

namespace Seahinet\Customer\Model;

use Seahinet\Customer\Model\Collection\Wishlist\Item as Collection;
use Seahinet\Customer\Model\Wishlist\Item as Model;
use Seahinet\Lib\Model\AbstractModel;

class Wishlist extends AbstractModel {

    protected function construct() {
        $this->init('wishlist', 'id', ['id', 'customer_id']);
    }

    public function getItems() {
        if ($this->getId()) {
            $items = new Collection;
            $items->where(['wishlist_id' => $this->getId()]);
            return $items;
        }
        return [];
    }

    public function addItem($item) {
        $item = new Model($item);
//         $product = new Product;
//         $product->load();
//         $item->setData([
//                 'store_id' => $product['store_id'],
//                 'product_name' => $product['name']
//         ]);
        $item->save();
    }

}
