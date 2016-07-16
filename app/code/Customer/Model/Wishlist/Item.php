<?php

namespace Seahinet\Customer\Model\Wishlist;

use Seahinet\Lib\Model\AbstractModel;

class Item extends AbstractModel {

    protected function construct() {
        $this->init('wishlist_item', 'id', [
            'id', 'wishlist_id', 'product_id', 'product_name',
            'store_id', 'qty', 'options', 'description'
        ]);
    }

}
