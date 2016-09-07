<?php

namespace Seahinet\Catalog\Model\Collection;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\Collection\Eav\Collection;

class Product extends Collection
{

    const ENTITY_TYPE = 'product';

    public function withStore($store = null)
    {
        if (is_null($store)) {
            $store = Bootstrap::getStore()->getId();
        } else if (is_object($store) || is_array($store)) {
            $store = $store['id'];
        }
        $this->select->where(['store_id' => $store]);
        return $this;
    }
	
	/** 
    * withInSales 
    * Get product obj in sales
    * 
    * @access public
    * @return object 
    */ 	
	public function withInSales($store = null)
    {
        if (is_null($store)) {
            $store = Bootstrap::getStore()->getId();
        } else if (is_object($store) || is_array($store)) {
            $store = $store['id'];
        }

		$this->select->where->nest->isNull('new_end')->or->greaterThanOrEqualTo('new_end', date('Y-m-d H:i:s'))->unnest;
		$this->select->where->equalTo('store_id',$store);
		$this->select->where->equalTo('status',1);
        return $this;
    }

}
