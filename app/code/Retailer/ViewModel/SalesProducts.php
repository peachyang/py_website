<?php

namespace Seahinet\Retailer\ViewModel;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Lib\Session\Segment;
use Seahinet\Catalog\Model\Product as Pmodel;
use Seahinet\Catalog\Model\Collection\Product as Pcollection;
use Seahinet\Catalog\Model\Collection\Category as Ccollection;
use Seahinet\Retailer\Model\Collection\ProductInCategoryCollection as Picollection;
use Seahinet\Catalog\Model\Warehouse;
use Zend\Db\Sql\Expression;

class SalesProducts extends Template
{
    protected $categories = null;
    
	/** 
    * getRetailerSalesProducts  
    * Get retailer's products in sales record by search condition
    * 
    * @access public 
    * @return object 
    */ 
	
	public function getRetailerSalesProducts($params = array())
	{
	    $user = (new Segment('customer'))->get('customer');
		$condition = !empty($params) ? $params : $this->getQuery();
        $sales_products = new Pcollection;
		$sales_products = $sales_products->withInSales();
		$where = new \Zend\Db\Sql\Where();
        $where->equalTo('status', 1);
        if($user->getRetailer()){
            $where->equalTo('store_id', $user->getRetailer()->offsetGet('store_id'));
        }
        
        if(!empty($condition['name'])){
            $where->like('name', '%'.$condition['name'].'%');
        }
        if(!empty($condition['sku'])){
            $where->like('sku',  '%'.$condition['sku']. '%');
        }
        if(!empty($condition['price_from'])){
            $where->greaterThanOrEqualTo('price',$condition['price_from']);
        }
		if(!empty($condition['price_to'])){
            $where->lessThanOrEqualTo('price',$condition['price_to']);
        }
        if(isset($condition['catalog']) && $condition['catalog'] != ''){
            $product_in_category_collection = new Picollection;
            $product_in_category_id = $product_in_category_collection->columns(['product_id'])->where(['category_id' => $condition['catalog']]);
            $where->in('id', $product_in_category_id);
        }
        if(isset($condition['recomend_status']) && $condition['recomend_status'] != ''){
            $where->equalTo('recommend',$condition['recomend_status']);
        }
        
		$sales_products->where($where)->order(['created_at'=>'DESC']);
		return $this->prepareCollection($sales_products,$condition);
	}

    /** 
    * unidimensional
    * Convert multi dimensional to unidimensional
    * 
    * @access protected 
    * @return object 
    */ 
    protected function unidimensional($array, $level)
    {
        static $result_array=array();
        echo $level;
        foreach($array as &$item){
            if(is_array($item)){
                $this->unidimensional($item, $level+1);
            } else{
                $item['level'] = $level;
                $result_array[]=$item;
            }
        }
        return $result_array;
    }
    
    /** 
    * getCategories  
    * Get main category list
    * 
    * @access public 
    * @return object 
    */ 
    public function getCategories()
    {
        if (is_null($this->categories)) {
            $collection = new Ccollection;
            $collection->order('parent_id ASC, sort_order ASC');
            $this->categories = [];
            foreach ($collection as $item) {
                $pid = (int) $item['parent_id'];
                if (!isset($this->categories[$pid])) {
                    $this->categories[$pid] = [];
                }
                $this->categories[$pid][] = $item;
            }
        }
        return $this->categories;
    }
    
    public function renderCategory($level = 0, $class=0)
    {
        $html = '';
        if (!empty($this->getCategories()[$level])) {
            foreach ($this->getCategories()[$level] as $category) {
                $html .= '<option  value="' .$category['id'] . '">' .str_repeat('&nbsp;&nbsp;', $class). $category['name'] . '</option>'.(isset($this->getCategories()[$category['id']]) ? $this->renderCategory($category['id'], $class+1)  : '') ;
            }
        }
        return $html;
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
        $limit = $condition['limit'] ?? 20;
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
    