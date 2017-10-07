<?php

namespace Seahinet\Admin\Controller\Customer;

use Exception;
use Seahinet\Customer\Model\Group as Model;
use Seahinet\Lib\Controller\AuthActionController;

class GroupController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_customer_group_list');
        return $root;
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_customer_group_edit');
        $model = new Model;
        if ($id = $this->getRequest()->getQuery('id')) {
            $model->load($id);
            $root->getChild('head')->setTitle('Edit Customer Group / Customer Management');
        } else {
            $root->getChild('head')->setTitle('Add New Customer Group / Customer Management');
        }
        $root->getChild('edit', true)->setVariable('model', $model);
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Customer\\Model\\Group', ':ADMIN/customer_group/');
    }

    public function saveAction()
    {
        return $this->doSave('\\Seahinet\\Customer\\Model\\Group', ':ADMIN/customer_group/', ['name']);
    }

}
