<?php

namespace Seahinet\Admin\Controller\Cms;

use Exception;
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
        return $this->doSave('\\Seahinet\\Cms\\Model\\Category', ':ADMIN/cms_category/', ['language_id', 'uri_key', 'name'], function($model, $data) {
                    if (!isset($data['parent_id']) || (int) $data['parent_id'] === 0) {
                        $model->setData('parent_id', null);
                    }
                }
        );
    }

}
