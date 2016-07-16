<?php

namespace Seahinet\Customer\Model\Collection\Wishlist;

use Seahinet\Lib\Model\AbstractCollection;

class Item extends AbstractCollection {

    protected function construct() {
        $this->init('wishlist_item');
    }

}
