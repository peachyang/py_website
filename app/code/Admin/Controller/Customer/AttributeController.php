<?php

namespace Seahinet\Admin\Controller\Customer;

use Exception;
use Seahinet\Lib\Model\Eav\Attribute as Model;
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
        return $this->doSave('\\Seahinet\\Lib\\Model\\Eav\\Attribute', ':ADMIN/customer_attribute/', [], function($modal, $data) {
                    $modal->setData('type_id', 1);
                    if (!isset($data['sort_order']) || !$data['sort_order']) {
                        $modal->setData('sort_order', 0);
                    }
                }
        );
    }

}
