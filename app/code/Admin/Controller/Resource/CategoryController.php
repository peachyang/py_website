<?php

namespace Seahinet\Admin\Controller\Resource;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\Resource\Model\Category as Model;

class CategoryController extends AuthActionController
{

    public function indexAction()
    {

        $root = $this->getLayout('admin_resource_category_list');
        return $root;
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_resource_category_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit Category / Resource Category / CMS');
        } else {
            $root->getChild('head')->setTitle('Add New Category / Resource Category / CMS');
        }
        return $root;
    }

    public function saveAction()
    {
        return $this->doSave('\\Seahinet\\Resource\\Model\\Category', ':ADMIN/resource_category/', ['language_id', 'code', 'name'], function($model, $data) {
                    if (!isset($data['parent_id']) || (int) $data['parent_id'] === 0) {
                        $model->setData('parent_id', null);
                    }
                    $user = (new Segment('admin'))->get('user');
                    if ($user->getStore()) {
                        $model->setData('store_id', $user->getStore()->getId());
                    } else if (!isset($data['store_id']) || (int) $data['store_id'] === 0) {
                        $model->setData('store_id', null);
                    }
                }
        );
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Resource\\Model\\Category', ':ADMIN/resource_category/');
    }

}
