<?php
namespace Seahinet\Retailer\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class StoreTemplateCollection extends AbstractCollection
{

    protected function construct()
    {
        $this->init('store_decoration_template', 'id', ['id', 'template_name','store_id']);

    }
	
     public function storeTemplateList($store = null)
    {
        if (is_null($store)) {
            $store = Bootstrap::getStore()->getId();
        } else if (is_object($store) || is_array($store)) {
            $store = $store['id'];
        }

		$this->select->where->equalTo('store_id',$store);
        return $this;
    }
    
}
