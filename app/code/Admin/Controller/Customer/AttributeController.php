<?php

namespace Seahinet\Admin\Controller\Customer;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\Model\Eav\Attribute as Model;
use Seahinet\Lib\Model\Eav\Type;
use Seahinet\Lib\Controller\AuthActionController;

class AttributeController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_customer_attribute_list');
        return $root;
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_customer_attribute_edit');
        $model = new Model;
        if ($id = $this->getRequest()->getQuery('id')) {
            $model->load($id);
            $root->getChild('head')->setTitle('Edit Customer Attribute / Customer Management');
        } else {
            $root->getChild('head')->setTitle('Add New Customer Attribute / Customer Management');
        }
        $root->getChild('edit', true)->setVariable('model', $model);
        $root->getChild('label', true)->setVariable('model', $model);
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Lib\\Model\\Eav\\Attribute', ':ADMIN/customer_attribute/');
    }

    public function saveAction()
    {
        return $this->doSave('\\Seahinet\\Lib\\Model\\Eav\\Attribute', ':ADMIN/customer_attribute/', [], function($model, $data) {
                    $type = new Type;
                    $type->load(Customer::ENTITY_TYPE, 'code');
                    $model->setData([
                        'code' => trim(preg_replace('/\W+/', '_', strtolower($data['code'])), '_'),
                        'type_id', $type->getId()
                    ]);
                }
        );
    }

}
