<?php

namespace Seahinet\Admin\Controller\Customer\Attribute;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Model\Eav\Type;

class GroupController extends AuthActionController
{

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Lib\\Model\\Eav\\Attribute\\Group', ':ADMIN/customer_attribute_set/');
    }

    public function saveAction()
    {
        return $this->doSave('\\Seahinet\\Lib\\Model\\Eav\\Attribute\\Group', ':ADMIN/customer_attribute_set/', ['name'], function($model, $data) {
                    $type = new Type;
                    $type->load(Customer::ENTITY_TYPE, 'code');
                    $model->setData('type_id', $type->getId());
                }
        );
    }

}
