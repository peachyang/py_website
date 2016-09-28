<?php

namespace Seahinet\Checkout\ViewModel\Cart;

use Seahinet\Catalog\Model\Collection\Product;
use Seahinet\Catalog\ViewModel\Product\Link;
use Seahinet\Customer\Model\Collection\Wishlist\Item as WishlistItem;
use Seahinet\Lib\Session\Segment;

class Wishlist extends Link
{

    public function getProducts()
    {
        $segment = new Segment('customer');
        if ($segment->get('hasLoggedIn')) {
            $items = new WishlistItem;
            $items->join('wishlist', 'wishlist.id=wishlist_item.wishlist_id', [], 'left')
                    ->columns(['product_id'])
                    ->where(['wishlist.customer_id' => $segment->get('customer')->getId()])
            ->where->isNotNull('product_id');
            $ids = [];
            foreach ($items as $item) {
                $ids[] = $item['id'];
            }
            if ($ids) {
                $products = new Product;
                $products->where(['status' => 1])->where->in('id', $ids);
                return $products;
            }
        }
        return [];
    }

}
