<?php

namespace Seahinet\Admin\Controller\Catalog;

use Exception;
use Seahinet\Catalog\Model\Product as Model;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Model\Collection\Eav\Attribute\Set;
use Seahinet\Lib\Model\Collection\Language;
use Seahinet\Lib\Model\Eav\Type;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;

class ProductController extends AuthActionController
{

    private $searchable = null;

    public function indexAction()
    {
        $root = $this->getLayout('admin_catalog_product_list');
        return $root;
    }

    public function listAction()
    {
        $root = $this->getLayout('admin_catalog_product_simple_list');
        return $root;
    }

    public function editAction()
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
            $root = $this->getLayout(!isset($query['attribute_set']) || !isset($query['product_type']) ? 'admin_catalog_product_beforeedit' : 'admin_catalog_product_edit_' . $query['product_type']);
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
                }
                if (empty($data['uri_key']) && !empty($data['name'])) {
                    $model->setData('uri_key', trim(strtolower(preg_replace('/\W+/', '-', rawurlencode($data['name']))), '-'));
                }
                $type = new Type;
                $type->load(Model::ENTITY_TYPE, 'code');
                $model->setData([
                    'type_id' => $type->getId()
                ]);
                $user = (new Segment('admin'))->get('user');
                if ($user->getStore()) {
                    if ($model->getId() && $model->offsetGet('store_id') != $user->getStore()->getId()) {
                        return $this->redirectReferer();
                    }
                    $model->setData('store_id', $user->getStore()->getId());
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
                    $languages = new Language;
                    $languages->columns(['id']);
                    $languages->load(true, false);
                    foreach ($languages as $language) {
                        $this->reindex($model->getId(), $language['id']);
                    }
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

    private function getSearchableAttributes()
    {
        if (is_null($this->searchable)) {
            $this->searchable = new Attribute;
            $this->searchable->columns(['code'])
                    ->join('eav_entity_type', 'eav_attribute.type_id=eav_entity_type.id', [], 'right')
                    ->where(['eav_entity_type.code' => Model::ENTITY_TYPE, 'searchable' => 1]);
        }
        return $this->searchable;
    }

    private function reindex($id, $languageId)
    {
        $model = new Model($languageId);
        $model->load($id);
        $indexer = $this->getContainer()->get('indexer');
        $values = [];
        foreach ($model->getCategories() as $category) {
            if ($category['uri_key']) {
                $record = $indexer->select('catalog_url', $languageId, ['category_id' => $category->getId(), 'product_id' => null]);
                if (count($record)) {
                    $values[] = ['category_id' => $category->getId(), 'product_id' => $id, 'path' => $record[0]['path'] . '/' . $model['uri_key']];
                }
            }
        }
        $indexer->replace('catalog_url', $languageId, $values, ['product_id' => $id]);
        $data = ['id' => $id, 'store_id' => $model['store_id'], 'data' => '|'];
        foreach ($this->getSearchableAttributes() as $attr) {
            $value = $model[$attr['code']];
            if ($value !== '' && $value !== null) {
                $data['data'] .= $value . '|';
            }
        }
        $indexer->replace('catalog_search', $languageId, [$data], ['id' => $id]);
    }

}
