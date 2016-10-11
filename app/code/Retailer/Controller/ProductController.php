<?php

namespace Seahinet\Retailer\Controller;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Session\Segment;
use Seahinet\Catalog\Model\Product as Model;
use Seahinet\Retailer\Model\Retailer as Retailer;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Model\Collection\Eav\Attribute\Set;
use Seahinet\Lib\Model\Eav\Type;

/**
 * Retailer submenu products management controller
 * 
 */
class ProductController extends AuthActionController
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
     * Show release product view
     * 
     * @access public 
     * @return object 
     */
    public function releaseAction()
    {
        //$user = (new Segment('customer'))->get('customer');
        //var_dump($user->getRetailer()->offsetGet("store_id"));
        $query = $this->getRequest()->getQuery();
        $model = new Model;
        if (isset($query['id'])) {
            $model->load($query['id']);
            $root = $this->getLayout('retailer_products_product_edit_' . $model['product_type_id']);
            $root->getChild('head')->setTitle('Edit Product / Product Management');
        } else {
            $model->setData('attribute_set_id', function() {
                $set = new Set;
                $set->join('eav_entity_type', 'eav_entity_type.id=eav_attribute_set.type_id', [], 'left')
                        ->where(['eav_entity_type.code' => Model::ENTITY_TYPE]);
                return $set->load()[0]['id'];
            });
            $root = $this->getLayout(!isset($query['attribute_set']) || !isset($query['product_type']) ? 'retailer_products_release' : 'retailer_products_product_edit_' . $query['product_type']);
            $root->getChild('head')->setTitle('Add New Product / Product Management');
        }
        $root->getChild("content")->getChild("main")->setVariable('model', $model);
        return $root;
    }

    /**
     * salesAction  
     * Show the list of under sale products
     * 
     * @access public 
     * @return object 
     */
    public function salesAction()
    {
        $root = $this->getLayout('retailer_sales_products');
        $root->getChild('main', true)->setVariable('subtitle', 'Sales of Product')->setVariable('filter', $this->getRequest()->getQuery());
        return $root;
    }

    /**
     * stockAction  
     * Show the list of products in stock
     * 
     * @access public 
     * @return object 
     */
    public function stockAction()
    {
        $root = $this->getLayout('retailer_product');
        $order = Array(
            'type' => 'stock'
        );
        $root->getChild('main', true)->setVariable('subtitle', 'Stock')->setVariable('order', $order);
        return $root;
    }

    /**
     * historyAction  
     * Show the list of history products record
     * 
     * @access public 
     * @return object 
     */
    public function historyAction()
    {
        $root = $this->getLayout('retailer_product');
        $order = Array(
            'type' => 'history'
        );
        $root->getChild('main', true)->setVariable('subtitle', 'History Record')->setVariable('order', $order);
        return $root;
    }

    /**
     * saveAction  
     * Save new product
     * 
     * @access public 
     * @return object 
     */
    public function saveAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $attributes = new Attribute;
            $attributes->withSet()->where([
                        'is_required' => 1,
                        'eav_attribute_set.id' => $data['attribute_set_id'],
                    ])->columns(['code'])
                    ->join('eav_entity_type', 'eav_attribute.type_id=eav_entity_type.id AND eav_entity_type.id=eav_attribute_set.type_id', [], 'right')
                    ->where(['eav_entity_type.code' => Model::ENTITY_TYPE]);
            $required = ['store_id', 'attribute_set_id'];
            $attributes->walk(function ($attribute) use (&$required) {
                $required[] = $attribute['code'];
            });
            $result = $this->validateForm($data, $required);
            if ($result['error'] === 0) {
                $model = new Model($this->getRequest()->getQuery('language_id', Bootstrap::getLanguage()->getId()), $data);
                if (empty($data['id'])) {
                    $model->setId(null);
                    $back_url = 'retailer/product/release/';
                }else{
                    if(empty($data['backurl'])){
                        $back_url = 'retailer/product/release/';
                    }else{
                        $back_url = $data['backurl'];
                    }
                }
                if (empty($data['uri_key'])) {
                    $model->setData('uri_key', strtolower(preg_replace('/\W/', '-', $data['name'])));
                }
                $type = new Type;
                $type->load(Model::ENTITY_TYPE, 'code');
                $model->setData([
                    'type_id' => $type->getId()
                ]);
                $user = (new Segment('customer'))->get('customer');
                $retailer = new Retailer;
                $retailer->load($user->getId(),'customer_id');
                if ($retailer['store_id']) {
                    if ($model->getId() && $model->offsetGet('store_id') == $retailer['store_id']) {
                        $model->setData('store_id', $retailer['store_id']);
                    }
                }
                if (empty($data['parent_id'])) {
                    $model->setData('parent_id', null);
                } else if (empty($data['uri_key'])) {
                    $model->setData('uri_key', trim(preg_replace('/\s+/', '-', $data['name'])), '-');
                } else {
                    $model->setData('uri_key', rawurlencode(trim(preg_replace('/\s+/', '-', $data['uri_key']), '-')));
                }
                try {
                    $model->save();
                    $result['message'][] = ['message' => $this->translate('An item has been saved successfully.'), 'level' => 'success'];
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['message'][] = ['message' => $this->translate('An error detected while saving. Please check the log report or try again.'), 'level' => 'danger'];
                    $result['error'] = 1;
                }
                $this->getContainer()->get('indexer')->reindex('catalog_url');
                $this->getContainer()->get('indexer')->reindex('catalog_search');
            }
        }
        return $this->response($result, $back_url, 'retailer');
    }

    /**
     * popupAction  
     * Popup resource management window
     * 
     * @access public 
     * @return object 
     */
    public function popupAction()
    {
        return $this->getLayout('retailer_popup_images_list');
    }

}
