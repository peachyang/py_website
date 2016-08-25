<?php

namespace Seahinet\Retailer\Controller;

use Exception;
use Seahinet\Retailer\Model\Retailer as Rmodel;
use Seahinet\Lib\Session\Segment;

/** 
* Retailer submenu goods management controller
* 
*/  
class GoodsController extends AuthActionController
{

    public function indexAction()
    {
        $segment = new Segment('customer');
        
        if ($customerId = $segment->get('customer')->getId()) {
            $customer = new Cmodel;
            $customer->load($customerId);
            $root = $this->getLayout('retailer_store_settings');
            $root->getChild('main', true)->setVariable('customer', $customer);
            return $root;
        }
        return $root;
    }
    
    /** 
    * releaseAction  
    * Show release good view
    * 
    * @access public 
    * @return object 
    */
    public function releaseAction()
    {
        $root = $this->getLayout('retailer_goods_release');
        return $root;
    }

}
