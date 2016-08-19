<?php
namespace Seahinet\Customer\ViewModel;
use Seahinet\Catalog\Model\Logview;
use Seahinet\Catalog\Model\Collection\Logview as Collection;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\Catalog\ViewModel\Product\Link;

class Logviews extends Template
{

    protected $products = null;


    public function getProducts()
    {

        if (is_null($this->products)) {
            $logview = new Collection;
            $segment = new Segment('customer');
            $logview->where(['customer_id' => $segment->get('customer')['id']])
                    ->order('created_at');
            if ($logview->count()) {
                return $logview;
            }
        }
        return NULL;
    }

}