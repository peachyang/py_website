<?php

namespace Seahinet\Retailer\Controller;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Session\Segment;
use Seahinet\Catalog\Model\Product as Model;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Model\Collection\Eav\Attribute\Set;
use Seahinet\Lib\Model\Eav\Type;

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
        $query = $this->getRequest()->getQuery();
        $model = new Model;
        if (isset($query['id'])) {
            $model->load($query['id']);
            $root = $this->getLayout('admin_catalog_product_edit_' . $model['product_type_id']);
            $root->getChild('head')->setTitle('Edit Product / Product Management');
        } else {
            $model->setData('attribute_set_id', function() {
                $set = new Set;
                $set->join('eav_entity_type', 'eav_entity_type.id=eav_attribute_set.type_id', [], 'left')
                        ->where(['eav_entity_type.code' => Model::ENTITY_TYPE]);
                return $set->load()[0]['id'];
            });
            $root = $this->getLayout(!isset($query['attribute_set']) || !isset($query['product_type']) ? 'retailer_goods_release' : 'retailer_goods_product_edit_' . $query['product_type']);
            $root->getChild('head')->setTitle('Add New Product / Product Management');
            $root->getChild('content')->getChild('main')->setVariable('model', $model);
        }
        //$root->setVariable('model', $model);
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
        $root = $this->getLayout('retailer_sales_products');
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
