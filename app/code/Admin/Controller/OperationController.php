<?php

namespace Seahinet\Admin\Controller;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Admin\Model\Operation as Model;

class OperationController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('admin_operation_list');
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_operation_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($id);
            if ($model['is_system']) {
                return $this->redirectReferer(':ADMIN/operation/');
            }
            $root->getChild('edit', true)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit Operation / Operation Management');
        } else {
            $root->getChild('head')->setTitle('Add New Operation / Operation Management');
        }
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Admin\\Model\\Operation', ':ADMIN/operation/');
    }

    public function saveAction()
    {
        return $this->doSave('\\Seahinet\\Admin\\Model\\Operation', ':ADMIN/operation/', ['name']);
    }

}
