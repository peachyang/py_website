<?php

namespace Seahinet\Customer\ViewModel;

use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\Log\Model\Collection\Visitor as Collection;

class ViewedProduct extends Template
{

    protected $products = null;

    public function getProducts()
    {
        if (is_null($this->products)) {
            $collection = new Collection;
            $segment = new Segment('customer');
            $collection->where(['customer_id' => $segment->get('customer')['id']])
                    ->order('created_at DESC')
            ->where->isNotNull('product_id');
            if ($collection->count()) {
                return $collection;
            }
        }
        return [];
    }

}
