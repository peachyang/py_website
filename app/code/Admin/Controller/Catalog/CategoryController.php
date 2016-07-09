<?php

namespace Seahinet\Admin\Controller\Catalog;

use Exception;
use Seahinet\Catalog\Model\Category as Model;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\Collection\Eav\Attribute\Set;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Model\Eav\Type;
use Seahinet\Lib\Session\Segment;

class CategoryController extends AuthActionController
{

    use \Seahinet\Lib\Traits\DB;

    public function indexAction()
    {
        $root = $this->getLayout('admin_catalog_category_list');
        return $root;
    }

    public function editAction()
    {
        $query = $this->getRequest()->getQuery();
        $root = $this->getLayout('admin_catalog_category_edit');
        $model = new Model;
        if (isset($query['id'])) {
            $model->load($query['id']);
            $root->getChild('head')->setTitle('Edit Category / Category Management');
        } else {
            $model->setData('attribute_set_id', function() {
                $set = new Set;
                $set->join('eav_entity_type', 'eav_entity_type.id=eav_attribute_set.type_id', [], 'left')
                        ->where(['eav_entity_type.code' => Model::ENTITY_TYPE]);
                return $set->load()[0]['id'];
            });
            $root->getChild('head')->setTitle('Add New Category / Category Management');
        }
        $root->getChild('edit', true)->setVariable('model', $model);
        return $root;
    }

    public function orderAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $languageId = Bootstrap::getLanguage()->getId();
            $model = new Model($languageId);
            $model->setTransaction(true);
            $this->beginTransaction();
            foreach ($data['id'] as $order => $id) {
                $model = clone $model;
                $model->load($id)->setData([
                    'sort_order' => $order,
                    'parent_id' => $data['order'][$order]? : null
                ])->save();
            }
            $this->commit();
        }
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Catalog\\Model\\Category', ':ADMIN/catalog_category/');
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
                $this->getContainer()->get('indexer')->reindex('catalog_url');
            }
        }
        return $this->response($result, ':ADMIN/catalog_category/');
    }

}