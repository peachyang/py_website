<?php

namespace Seahinet\Customer\ViewModel;

use Seahinet\Catalog\Model\Collection\ViewedProduct as Collection;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\ViewModel\Template;

class ViewedProduct extends Template
{

    protected $products = null;

    public function getProducts()
    {
        if (is_null($this->products)) {
            $collection = new Collection;
            $segment = new Segment('customer');
            $collection->where(['customer_id' => $segment->get('customer')['id']])
                    ->order('updated_at DESC, created_at DESC');
            if ($collection->count()) {
                return $collection;
            }
        }
        return [];
    }

}
