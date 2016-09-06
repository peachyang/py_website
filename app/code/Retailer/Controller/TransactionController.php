<?php

namespace Seahinet\Retailer\Controller;

use Seahinet\Lib\Session\Segment;

/** 
* Retailer submenu transaction controller
* 
*/  
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
        $sales_id = $this->getRequest()->getQuery('sales_id');
        if(empty($sales_id) || !is_numeric($sales_id)){
            return $this->redirect('retailer/transaction/goods/');
        }
        $root = $this->getLayout('retailer_order_view');
        return $root;
    }
    
    /** 
    * commentAction  
    * View the comment information
    * 
    * @access public 
    * @return object 
    */ 
    public function commentAction()
    {
        $root = $this->getLayout('retailer_comment');
        return $root;
    }
    
    /** 
    * commentAction  
    * View the comment information
    * 
    * @access public 
    * @return object 
    */ 
    public function afterAction()
    {
        $root = $this->getLayout('retailer_after');
        return $root;
    }
    
    /** 
    * afterviewAction  
    * View the after service information
    * 
    * @access public 
    * @return object 
    */ 
    public function afterviewAction()
    {
        $root = $this->getLayout('retailer_after_view');
        return $root;
    }

}
