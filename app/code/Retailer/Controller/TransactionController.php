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
    * productsAction  
    * Show retailer sold products list
    * 
    * @access public 
    * @return object 
    */ 
    public function productAction()
    {
        $root = $this->getLayout('retailer_product');
        $order = Array(
            'type' => 'sold'
        );
        $root->getChild('main', true)->setVariable('subtitle', 'Sold Product')->setVariable('filter', $this->getRequest()->getQuery());
        return $root;
    }
    
    /** 
    * orderviewAction  
    * View the order infomation
    * 
    * @access public 
    * @return object 
    */ 
    public function orderviewAction()
    {
        $order_id = $this->getRequest()->getQuery('order_id');
        if(empty($order_id) || !is_numeric($order_id)){
            return $this->redirect('retailer/transaction/products/');
        }
        $root = $this->getLayout('retailer_order_view');
        return $root;
    }
    
    /** 
    * commentAction  
    * View the comment infomation
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
    * afterAction  
    * View the comment infomation
    * 
    * @access public 
    * @return object 
    */ 
    public function afterAction()
    {
        $root = $this->getLayout('retailer_after');
        $root->getChild('main',true)->setVariable('subtitle', 'After Service')->setVariable('history_sold', 'History after Sale Service');
        return $root;
    }
    
    /** 
    * afterviewAction  
    * View the after service infomation
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
