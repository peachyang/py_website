<?php

namespace Seahinet\Catalog\ViewModel\Product;

use Seahinet\Catalog\Model\Collection\Product\Review as Collection;
use Seahinet\Lib\Session\Segment;

class Review extends View
{

    public function getInquiries()
    {
        $reviews = new Collection;
        $reviews->where([
                    'product_id' => $this->getProduct()->getId(),
                    'order_id' => null
                ])
                ->order('created_at DESC')
                ->where->isNotNull('reply');
        return $reviews;
    }

    public function getReviews()
    {
        $reviews = new Collection;
        $reviews->where(['product_id' => $this->getProduct()->getId()])
                ->order('created_at DESC')
                ->where->isNotNull('order_id');
        return $reviews;
    }

    public function canReview()
    {
        $segment = new Segment('customer');
        return $this->getConfig()['catalog/review/allow_guests'] || $segment->get('hasLoggedIn');
    }

}
