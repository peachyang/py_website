<?php

namespace Seahinet\Sales\ViewModel;

use Seahinet\Catalog\Model\Collection\Product\Rating;
use Seahinet\Lib\ViewModel\Template;

class Review extends Template
{

    protected $rating = null;
    protected $orderRating = null;
    protected $productRating = null;

    public function getRating()
    {
        if (is_null($this->rating)) {
            $this->rating = new Rating;
            $this->rating->where(['status' => 1])
                    ->order('type DESC, id ASC');
        }
        return $this->rating;
    }

    public function getOrderRating()
    {
        if (is_null($this->orderRating)) {
            $this->orderRating = [];
            foreach ($this->getRating() as $rating) {
                if ($rating['type']) {
                    $this->orderRating[] = $rating->toArray();
                } else {
                    break;
                }
            }
        }
        return $this->orderRating;
    }

    public function getProductRating()
    {
        if (is_null($this->productRating)) {
            $this->productRating = [];
            foreach ($this->getRating() as $rating) {
                if (!$rating['type']) {
                    $this->productRating[] = $rating->toArray();
                }
            }
        }
        return $this->productRating;
    }

}
