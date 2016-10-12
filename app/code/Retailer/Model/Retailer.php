<?php

namespace Seahinet\Retailer\Model;

use Seahinet\Lib\Model\{
    AbstractModel,
    Store
};

class Retailer extends AbstractModel
{

    protected $store = null;

    protected function construct()
    {
        $this->init('retailer', 'id', ['id', 'customer_id', 'store_id', 'description', 'contact', 'keywords', 'address', 'tel', 'uri_key', 'profile', 'watermark', 'banner']);
    }

    public function getStore()
    {
        if (is_null($this->store) && $this->offsetGet('store_id')) {
            $store = new Store;
            $store->load($this->offsetGet('store_id'));
            if ($store->getId()) {
                $this->store = $store;
            }
        }
        return $this->store;
    }

}
