<?php

namespace Seahinet\Admin\Controller\Cms;

use Seahinet\Cms\Model\Category as Model;
use Seahinet\Lib\Controller\AuthActionController;

class CategoryController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_cms_category_list');
        return $root;
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_cms_category_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit Category / CMS');
        } else {
            $root->getChild('head')->setTitle('Add New Category / CMS');
        }
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Cms\\Model\\Category', ':ADMIN/cms_category/');
    }

    public function saveAction()
    {
        $response = $this->doSave('\\Seahinet\\Cms\\Model\\Category', ':ADMIN/cms_category/', ['language_id', 'uri_key', 'name'], function($model, $data) {
            if (empty($data['parent_id'])) {
                $model->setData('parent_id', null);
            }
        }, false, [$this, 'reindex']);
        return $response;
    }

    protected function reindex($model)
    {
        $tmp = $model;
        $path = [$model['uri_key']];
        while (($tmp = $tmp->getParentCategory()) && $tmp['uri_key']) {
            array_unshift($path, $tmp['uri_key']);
        }
        $path = implode('/', $path);
        $values = [['page_id' => null, 'category_id' => $model->getId(), 'path' => $path]];
        foreach ($model->getPages() as $page) {
            $values[] = ['page_id' => $page['id'], 'category_id' => $model->getId(), 'path' => $path . '/' . $page['uri_key']];
        }
        foreach ((array) $model['language_id'] as $languageId) {
            $this->getContainer()->get('indexer')->replace('cms_url', $languageId, $values, ['category_id' => $model->getId()]);
            foreach ($model->getChildrenCategories() as $child) {
                $this->reindex($child);
            }
        }
    }

}
