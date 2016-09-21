<?php

namespace Seahinet\Retailer\ViewModel;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Lib\Session\Segment;
use Seahinet\Catalog\Model\Product as Pmodel;
use Seahinet\Catalog\Model\Collection\Product as Pcollection;
use Seahinet\Catalog\Model\Warehouse;
use Zend\Db\Sql\Expression;

class SalesProducts extends Template
{
	/** 
    * getRetailerSalesProducts  
    * Get retailer's products in sales record by search condition
    * 
    * @access public 
    * @return object 
    */ 
	
	public function getRetailerSalesProducts($params = array())
	{
		$condition = !empty($params) ? $params : $this->getQuery();
        $sales_products = new Pcollection;
		$sales_products = $sales_products->withInSales();
		$where = new \Zend\Db\Sql\Where();

        if(!empty($condition['name'])){
            $where->like('name', '%'.$condition['name'].'%');
        }
        if(!empty($condition['price_from'])){
            $where->greaterThanOrEqualTo('price',$condition['price_from']);
        }
		if(!empty($condition['price_to'])){
            $where->lessThanOrEqualTo('price',$condition['price_to']);
        }
		
		$sales_products->where($where);		
		return $this->prepareCollection($sales_products,$condition);
		
	}
	
	
	/**
	 * getInventory 
     * @access public
     * @param int $productID product id
     * @return object 
	 */
	public function getInventory($productId, $sku = '',$warehouse=1)
	{
		$warehouse = (new Warehouse)->setId($warehouse);
		$warehouse_qty = $warehouse->getInventory($productId, $sku);
		if(!empty($warehouse_qty['qty']))
			return $warehouse_qty['qty'];
		else
			return 0;
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
     * Handle sql for collection
     * 
     * @param AbstractCollection $collection
     * @return AbstractCollection
     */
    protected function prepareCollection($collection = null,$params = array())
    {
        if (is_null($collection)) {
            return [];
        }
        $condition = !empty($params) ? $params : $this->getQuery();
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
    



    
}
    