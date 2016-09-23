<?php

namespace Seahinet\Checkout\ViewModel\Cart;

use Seahinet\Catalog\Model\Collection\Product;
use Seahinet\Catalog\ViewModel\Product\Link;
use Seahinet\Customer\Model\Collection\Wishlist\Item;
use Seahinet\Lib\Session\Segment;
use Zend\Db\Sql\Predicate\In;

class Wishlist extends Link
{

    public function getProducts()
    {
        $segment = new Segment('customer');
        if ($segment->get('hasLoggedIn')) {
            $items = new Item;
            $items->join('wishlist', 'wishlist.id=wishlist_item.wishlist_id', [], 'left')
                    ->columns(['product_id'])
                    ->where(['wishlist.customer_id' => $segment->get('customer')->getId()])
                    ->where->isNotNull('product_id');
            $ids = [];
            foreach ($items as $item) {
                $ids[] = $item['id'];
            }
            $products = new Product;
            $products->where(new In('id', array_keys($ids)));
            return $products;
        }
        return [];
    }

}
