<?php

namespace Seahinet\Admin\Controller\Catalog;

use Seahinet\Catalog\Model\Product as Model;
use Seahinet\Lib\Model\Collection\Eav\Attribute\Set;
use Seahinet\Lib\Controller\AuthActionController;

class ProductController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_catalog_product_list');
        return $root;
    }

    public function editAction()
    {
        $query = $this->getRequest()->getQuery();
        $root = $this->getLayout(!isset($query['id']) && !isset($query['attribute_set']) ? 'admin_catalog_product_beforeedit' : 'admin_catalog_product_edit');
        $model = new Model;
        if (isset($query['id'])) {
            $model->load($query['id']);
            $root->getChild('head')->setTitle('Edit Product / Product Management');
        } else {
            $model->setData('attribute_set_id', function() {
                $set = new Set;
                $set->join('eav_entity_type', 'eav_entity_type.id=eav_attribute_set.type_id', [], 'left')
                        ->where(['eav_entity_type.code' => Model::ENTITY_TYPE]);
                return $set->load()[0]['id'];
            });
            $root->getChild('head')->setTitle('Add New Product / Product Management');
        }
        $root->getChild('edit', true)->setVariable('model', $model);
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Catalog\\Model\\Product', ':ADMIN/catalog_product/');
    }

    public function saveAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $attributes = new Attribute;
            $attributes->withSet()->where([
                        'is_required' => 1
                    ])->columns(['code'])
                    ->join('eav_entity_type', 'eav_attribute.type_id=eav_entity_type.id AND eav_entity_type.id=eav_attribute_set.type_id', [], 'right')
                    ->where(['eav_entity_type.code' => Model::ENTITY_TYPE]);
            $required = ['store_id'];
            $setId = 0;
            $attributes->walk(function ($attribute) use (&$required, &$setId) {
                $required[] = $attribute['code'];
                if (!$setId) {
                    $setId = $attribute['attribute_set_id'];
                }
            });
            $result = $this->validateForm($data, $required);
            if ($result['error'] === 0) {
                $model = new Model($this->getRequest()->getQuery('language_id', Bootstrap::getLanguage()->getId()), $data);
                if (!isset($data['id']) || (int) $data['id'] === 0) {
                    $model->setId(null);
                }
                $type = new Type;
                $type->load(Model::ENTITY_TYPE, 'code');
                $model->setData([
                    'type_id' => $type->getId(),
                    'attribute_set_id' => $setId
                ]);
                $user = (new Segment('admin'))->get('user');
                if ($user->getStore()) {
                    $model->setData('store_id', $user->getStore()->getId());
                }
                if (empty($data['parent_id'])) {
                    $model->setData('parent_id', null);
                } else if (empty($data['uri_key'])) {
                    $model->setData('uri_key', trim(strtolower(preg_replace('/\W+/', '-', $data['name']))), '-');
                }
                try {
                    $model->save();
                    $result['message'][] = ['message' => $this->translate('An item has been saved successfully.'), 'level' => 'success'];
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['message'][] = ['message' => $this->translate('An error detected while saving. Please check the log report or try again.'), 'level' => 'danger'];
                    $result['error'] = 1;
                }
            }
        }
        return $this->response($result, ':ADMIN/catalog_product/');
    }

}
