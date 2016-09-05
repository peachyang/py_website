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

    public function addItem($data)
    {
        $item = new Model($data);
        $product = new Product;
        $product->load($data['product_id'], 'id');
        $item->setData([
            'id' => null,
            'wishlist_id' => $this->getId(),
            'store_id' => $data['store_id'],
            'product_name' => $product['name'],
            'description' => preg_replace('/\<[^\>]+\>/', '', $product['description']),
            'price' => isset($data['base_price']) ? $data['base_price'] : $product->getFinalPrice($data['qty'], false)
        ]);
        $item->save();
        $this->flushList('wishlist');
        return $this;
    }

}
