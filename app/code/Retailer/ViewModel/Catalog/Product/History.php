<?php

namespace Seahinet\Retailer\ViewModel\Catalog\Product;

use Seahinet\Catalog\Model\Collection\Product as Collection;

class History extends AbstractProduct
{

    protected $actions = ['resell'];

    public function resell($item = null)
    {
        return '<a data-method="post" href="' . $this->getBaseUrl('retailer/product/resell/') .
                ($item ? '" data-params="id=' . $item['id'] . '&csrf=' . $this->getCsrfKey() . '"' :
                '" class="btn" data-serialize="#products-list"')
                . '>' . $this->translate('Resell') . '</a>';
    }

    public function getProducts()
    {
        $collection = new Collection;
        $collection->where([
            'store_id' => $this->getRetailer()['store_id'],
            'status' => 0
        ]);
        $this->filter($collection);
        return $collection;
    }

}
