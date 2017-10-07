<?php

namespace Seahinet\Admin\Controller\Customer\Attribute;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Model\Eav\Type;
use Seahinet\Lib\Model\Eav\Attribute\Set as Model;

class SetController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_customer_attribute_set_list');
        return $root;
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_customer_attribute_set_edit');
        $model = new Model;
        if ($id = $this->getRequest()->getQuery('id')) {
            $model->load($id);
            $root->getChild('head')->setTitle('Edit Customer Attribute Set / Customer Management');
        } else {
            $root->getChild('head')->setTitle('Add New Customer Attribute Set / Customer Management');
        }
        $root->getChild('edit', true)->setVariable('model', $model);
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Lib\\Model\\Eav\\Attribute\\Set', ':ADMIN/customer_attribute_set/');
    }

    public function saveAction()
    {
        $response = $this->doSave('\\Seahinet\\Lib\\Model\\Eav\\Attribute\\Set', ':ADMIN/customer_attribute_set/', ['name'], function($model, $data) {
            $type = new Type;
            $type->load(Customer::ENTITY_TYPE, 'code');
            $model->setData('type_id', $type->getId());
        }
        );
        return $response;
    }

}
