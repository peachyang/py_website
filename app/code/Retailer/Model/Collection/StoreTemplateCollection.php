<?php
namespace Seahinet\Retailer\Model\Collection;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\AbstractCollection;

class StoreTemplateCollection extends AbstractCollection
{

    protected function construct()
    {
        $this->init('store_decoration_template', 'id', ['id', 'template_name','store_id']);

    }
	
    public function storeTemplateList($store = null,$status='')
    {
        if (is_null($store)) {
            $store = Bootstrap::getStore()->getId();
        } else if (is_object($store) || is_array($store)) {
            $store = $store['id'];
        }
			
		if(!empty($status))
			$this->select->where->equalTo('store_id',$store)->equalTo('status',$status);
		else
			$this->select->where->equalTo('store_id',$store);
        return $this;
    }
	
	
    
}
