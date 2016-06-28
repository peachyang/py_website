<?php

namespace Seahinet\Admin\Controller\Catalog\Attribute;

use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Model\Eav\Type;

class GroupController extends AuthActionController
{

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Lib\\Model\\Eav\\Attribute\\Group', ':ADMIN/catalog_attribute_set/');
    }

    public function saveAction()
    {
        return $this->doSave('\\Seahinet\\Lib\\Model\\Eav\\Attribute\\Group', ':ADMIN/catalog_attribute_set/', ['name'], function($model, $data) {
                    $type = new Type;
                    $type->load(Product::ENTITY_TYPE, 'code');
                    $model->setData('type_id', $type->getId());
                }
        );
    }

}
