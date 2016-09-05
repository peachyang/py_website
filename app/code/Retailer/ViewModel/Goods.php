<?php

namespace Seahinet\Retailer\ViewModel;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Lib\Session\Segment;
use Seahinet\Retailer\Model\Collection\Goods as Collection;
use Seahinet\Sales\Model\Collection\Order as Ocollection;
use Seahinet\Sales\Model\Collection\Order\Item as Icollection;
use Seahinet\Customer\Model\Customer as Cmodel;
use Seahinet\Catalog\Model\Product as Pmodel;
use Seahinet\Sales\Model\Collection\Order\Status as Scollection;

class Goods extends Template
{
    /** 
    * getRetailerTransaction  
    * Get retailer's transaction record by search condition
    * 
    * @access public 
    * @return object 
    */ 
    public function getRetailerTransaction()
    {
        $condition = $this->getQuery();
        
        
        //$collection = new Collection;
       // $transaction = $collection->getRetailerTransaction();
       $sales_order_collection = new Ocollection;
       $segment = new Segment('customer');
       
       //Generate table name used for join
       //$language_id = $segment->get('customer')['language_id'];
       //$customer_table = 'customer_'.$language_id.'_index';
       //$sales_order_collection->join(['c' => $customer_table], 'c.id = sales_order.customer_id', ['username'], 'left')
        $where = new \Zend\Db\Sql\Where();
        if(!empty($condition['id'])){
            $where->like('sales_order.increment_id', '%'.$condition['id'].'%');
        }
        if(!empty($condition['order_status']) && $condition['order_status'] > 0){
            $where->equalTo('sales_order.status_id', $condition['order_status']);
        }
        if(!empty($condition['add_time_from']) && !empty($condition['add_time_to'])){
            $where->between('sales_order.created_at', $condition['add_time_from'], $condition['add_time_to']);
        }
        $sales_order_collection
        ->where($where)
//      ->where(['sales_order.store_id' => $segment->get('customer')['store_id']])
        ->join(['sos' => 'sales_order_status'], 'sos.id = sales_order.status_id', ['status_name' => 'name'], 'left')
        ->order(['sales_order.created_at'=>'DESC']);
        $sales_order_collection = $this->prepareCollection($sales_order_collection);
//      if(!empty($condition['limit'])){
//          $sales_order_collection->limit((int) $condition['limit']);
//      }
//      unset($condition['limit']);
       
        //Debug code
//      $adapter = $this->getContainer()->get('dbAdapter');
//      $sql = $sales_order_collection->getSqlString($adapter->getPlatform(), $adapter::QUERY_MODE_EXECUTE);
//      echo "<pre>";
//      print_r($sql);
//      echo "</pre>";
//      exit();
       
       
       //Sub query to select order id from sales_order table
       $sales_order_collection2 = new Ocollection;
       $order_id_select = $sales_order_collection2->columns(['id'])->where(['sales_order.store_id' => $segment->get('customer')['store_id']]);
       
       //Get product list in the order
       $item_collection = new Icollection;
       $where = new \Zend\Db\Sql\Where();
       $where->in('order_id', $order_id_select);
       //$item_collection->where(['order_id' => $order_id_select]);
       $item_collection->where($where);
       
       
       //Seperate product by order id
       $product_list = array();
       foreach($item_collection as $item){
            $product_list[$item['order_id']][] = $item;
        }
        //Combine product list to sales order
       foreach($sales_order_collection as &$order){
           $order["items"] = $product_list[$order['id']];
       }

        //return $segment->get('customer');
        return $sales_order_collection;
    }

    /** 
    * getCurrency  
    * Get price with currency
    * 
    * @access public 
    * @return object 
    */ 
    public function getCurrency()
    {
        return $this->getContainer()->get('currency');
    }
    
    /** 
    * getCustomerByID  
    * Get customer by customer id
    * 
    * @access public 
    * @param int $customerID customer id
    * @return object 
    */ 
    public function getCustomerByID($customerID){
        $customer_model = new Cmodel;
        $customer = $customer_model->load($customerID);
        return $customer;
    }
    
    /** 
    * getProduct  
    * Get product obj by product id
    * 
    * @access public
    * @param int $productID product id
    * @return object 
    */ 
    public function getProduct($productID){
        $product_model = new Pmodel;
        $product = $product_model->load($productID);
        return $product;
    }
    
    /**
     * Get current url
     * 
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->getUri()->withQuery('')->withFragment('')->__toString();
    }
    
    /** 
    * getAllSalesStatus  
    * Get all sales status list or specific sales status by status id
    * 
    * @access public
    * @param int $statusID sales status id
    * @return object 
    */ 
    public function getAllSalesStatus($statusID = NULL){
        $status_collection = new Scollection;
        if(!empty($statusID)){
            $status_collection->where('id', $statusID);
        }else{
            $status_collection->order('id');
        }
        return $status_collection;
    }
    
    /**
     * Handle sql for collection
     * 
     * @param AbstractCollection $collection
     * @return AbstractCollection
     */
    protected function prepareCollection($collection = null)
    {
        if (is_null($collection)) {
            return [];
        }
        $condition = $this->getQuery();
        $limit = isset($condition['limit']) ? $condition['limit'] : 20;
        if (isset($condition['page'])) {
            $collection->offset(($condition['page'] - 1) * $limit);
            unset($condition['page']);
        }
        $collection->limit((int) $limit);
        unset($condition['limit']);
        if (isset($condition['asc'])) {
            $collection->order((strpos($condition['asc'], ':') ?
                            str_replace(':', '.', $condition['asc']) :
                            $condition['asc']) . ' ASC');
            unset($condition['asc']);
        } else if (isset($condition['desc'])) {
            $collection->order((strpos($condition['desc'], ':') ?
                            str_replace(':', '.', $condition['desc']) :
                            $condition['desc']) . ' DESC');
            unset($condition['desc']);
        }
        return $collection;
    }

    /**
     * {@inhertdoc}
     */
    protected function getRendered($template)
    {
        $collection = $this->prepareCollection();
        if ($collection instanceof AbstractCollection) {
            $collection->load();
        }
        $this->setVariable('collection', $collection);
        return parent::getRendered($template);
    }
    
}
    