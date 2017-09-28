<?php

namespace Seahinet\Article\ViewModel\Product;

use Seahinet\Article\Model\Collection\Product\Review as Collection;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\ViewModel\Template;

class Review extends Template
{

    public function getInquiries()
    {
        $reviews = new Collection;
        $reviews->where([
                    'product_id' => $this->getVariable('id'),
                    'order_id' => null
                ])
                ->order('created_at DESC')
                ->limit(10)
                ->offset((int) $this->getQuery('page', 1) * 10 - 10)
        ->where->isNotNull('reply');
        return $reviews;
    }

    public function getReviews()
    {
        $reviews = new Collection;
        $reviews->where(['product_id' => $this->getVariable('id')])
                ->order('created_at DESC')
                ->limit(10)
                ->offset((int) $this->getQuery('page', 1) * 10 - 10)
        ->where->isNotNull('order_id');
        return $reviews;
    }

    public function canReview()
    {
        $segment = new Segment('customer');
        return $this->getConfig()['article/review/allow_guests'] || $segment->get('hasLoggedIn');
    }

}
