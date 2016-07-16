<?php

namespace Seahinet\Customer\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Wishlist extends AbstractCollection {

    protected function construct() {
        $this->init('wishlist');
    }

    protected function beforeLoad() {
        $this->select->join('wishlist_item', 'wishlist_item.wishlist_id=wishlist.id');
        parent::beforeLoad();
    }

}
