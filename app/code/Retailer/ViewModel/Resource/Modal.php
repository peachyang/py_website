<?php

namespace Seahinet\Retailer\ViewModel\Resource;

use Seahinet\Resource\Source\Category;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Source\Store;
use Seahinet\Lib\ViewModel\Template;

class Modal extends Template
{

    public function getCategorySource()
    {
        return (new Category)->getSourceArray();
    }

    public function getStore()
    {
        $segment = new Segment('customer');
        $store_id = $segment->get('customer')->offsetGet('store_id');
        if (!empty($store_id)) {
            return $store_id;
        } else {
            return (new Store)->getSourceArray();
        }
    }

}
