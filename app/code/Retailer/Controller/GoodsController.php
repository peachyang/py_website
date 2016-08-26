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
    
    /** 
    * salesAction  
    * Show the list of under sale goods
    * 
    * @access public 
    * @return object 
    */
    public function salesAction()
    {
        $root = $this->getLayout('retailer_goods');
        $order = Array(
            'type' => 'sales'
        );
        $root->getChild('main', true)->setVariable('subtitle', 'Sales of Goods')->setVariable('order', $order);
        return $root;
    }
    
    /** 
    * stockAction  
    * Show the list of goods in stock
    * 
    * @access public 
    * @return object 
    */
    public function stockAction()
    {
        $root = $this->getLayout('retailer_goods');
        $order = Array(
            'type' => 'stock'
        );
        $root->getChild('main', true)->setVariable('subtitle', 'Stock')->setVariable('order', $order);
        return $root;
    }
    
    /** 
    * historyAction  
    * Show the list of history goods record
    * 
    * @access public 
    * @return object 
    */
    public function historyAction()
    {
        $root = $this->getLayout('retailer_goods');
        $order = Array(
            'type' => 'history'
        );
        $root->getChild('main', true)->setVariable('subtitle', 'History Record')->setVariable('order', $order);
        return $root;
    }

}
