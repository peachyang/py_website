<?php

namespace Seahinet\Retailer\Controller;

use Exception;
use Seahinet\Retailer\Model\Retailer as Rmodel;
use Seahinet\Lib\Session\Segment;

class TransactionController extends AuthActionController
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
    * goodsAction  
    * Show retailer sold goods list
    * 
    * @access public 
    * @return object 
    */ 
    public function goodsAction()
    {
        $root = $this->getLayout('retailer_goods');
        $order = Array(
            'type' => 'sold'
        );
        $root->getChild('main', true)->setVariable('subtitle', 'Sold Goods')->setVariable('order', $order);
        return $root;
    }
    
    /** 
    * orderviewAction  
    * View the order information
    * 
    * @access public 
    * @return object 
    */ 
    public function orderviewAction()
    {
        $root = $this->getLayout('retailer_order_view');
        return $root;
    }

}
