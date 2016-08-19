<?php

namespace Seahinet\Customer\ViewModel;

use Seahinet\Catalog\Model\Logview;
use Seahinet\Catalog\Model\Collection\Logview as Collection;
use Seahinet\Catalog\ViewModel\Product\Link;
use Seahinet\Lib\Session\Segment;

class Logview extends Collection
{

    protected $products = null;

    public function getProducts()
    {
        
        if ($this->products()) {
            $logview = new Collection;
            $segment = new Segment('customer');
            $logview->where(['customer_id' => $segment->get('customer')['id']]);
            if ($logview->count()) {
                return $logview;
            }
        }
        return NULL;
    }

}
