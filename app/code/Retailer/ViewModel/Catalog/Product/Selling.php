<?php

namespace Seahinet\Retailer\ViewModel\Catalog\Product;

use Seahinet\Catalog\Model\Collection\Product as Collection;
use Zend\Db\Sql\Select;

class Selling extends AbstractProduct
{

    protected $actions = ['withdraw', 'delete', 'recommend', 'cancelRecommendation'];

    public function withdraw($item = null)
    {
        return '<a data-method="post" href="' . $this->getBaseUrl('retailer/product/withdraw/') .
                ($item ? '" data-params="id=' . $item['id'] . '&csrf=' . $this->getCsrfKey() . '"' :
                '" class="btn" data-serialize="#products-list"')
                . '>' . $this->translate('Withdraw') . '</a>';
    }

    public function delete($item = null)
    {
        return '<a data-method="post" href="' . $this->getBaseUrl('retailer/product/delete/') .
                ($item ? '" data-params="id=' . $item['id'] . '&csrf=' . $this->getCsrfKey() . '"' :
                '" class="btn" data-serialize="#products-list"')
                . '>' . $this->translate('Delete') . '</a>';
    }

    public function recommend($item = null)
    {
        return !$item || $item['recommended'] == 0 ? ('<a data-method="post" href="' . $this->getBaseUrl('retailer/product/recommend/') .
                ($item ? '" data-params="id=' . $item['id'] . '&csrf=' . $this->getCsrfKey() . '"' :
                '" class="btn" data-serialize="#products-list"')
                . '>' . $this->translate('Recommend') . '</a>') : '';
    }

    public function cancelRecommendation($item = null)
    {
        return !$item || $item['recommended'] == 1 ? ('<a data-method="post" href="' . $this->getBaseUrl('retailer/product/cancelRecommend/') .
                ($item ? '" data-params="id=' . $item['id'] . '&csrf=' . $this->getCsrfKey() . '"' :
                '" class="btn" data-serialize="#products-list"')
                . '>' . $this->translate('Cancel Recommendation') . '</a>') : '';
    }

    public function getProducts()
    {
        $collection = new Collection;
        $collection->where([
            'store_id' => $this->getRetailer()['store_id'],
            'status' => 1
        ])->order('id DESC');
        $stock = new Select('warehouse_inventory');
        $stock->columns(['product_id'])
                ->where(['status' => 1])
                ->group('product_id');
        $collection->in('id', $stock);
        $this->filter($collection);
        return $collection;
    }

}
