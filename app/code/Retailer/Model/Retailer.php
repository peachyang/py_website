<?php

namespace Seahinet\Retailer\Model;

use Seahinet\Catalog\Model\Collection\Product\Rating;
use Seahinet\Lib\Model\{
    AbstractModel,
    Store
};
use Zend\Db\Sql\Expression;

class Retailer extends AbstractModel
{

    protected $store = null;

    protected function construct()
    {
        $this->init('retailer', 'id', ['id', 'customer_id', 'store_id', 'description', 'contact', 'keywords', 'address', 'tel', 'uri_key', 'profile', 'watermark', 'banner']);
    }

    public function getStore()
    {
        if (is_null($this->store) && !empty($this->storage['store_id'])) {
            $store = new Store;
            $store->load($this->storage['store_id']);
            if ($store->getId()) {
                $this->store = $store;
            }
        }
        return $this->store;
    }

    public function getStoreUrl()
    {
        if (!empty($this->storage['uri_key'])) {
            return 'store/' . $this->storage['uri_key'] . '.html';
        }
        return '';
    }

    public function getRatings()
    {
        if (!empty($this->storage['store_id'])) {
            $collection = new Rating;
            $collection->columns(['id', 'title', 'type'])
                    ->join('review_rating', 'review_rating.rating_id=rating.id', ['value' => new Expression('avg(value)')], 'left')
                    ->join('review', 'review.id=review_rating.review_id', [], 'left')
                    ->join('product_entity', 'product_entity.id=review.product_id', [], 'left')
                    ->where([
                        'product_entity.store_id' => $this->storage['store_id'],
                        'rating.status' => 1
                    ])->group(['rating.id', 'rating.title', 'rating.type']);
            return $collection;
        }
        return [];
    }

}
